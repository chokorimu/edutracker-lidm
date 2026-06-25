# EduTrack — Deep Bug Analysis Report

This document outlines critical performance bugs, logic errors, and scalability issues found in the codebase, including how to fix them.

---

## 1. Catastrophic N+1 in `BebanCalculator::weeklyLoadForCourse`

**File**: `app/Services/BebanCalculator.php`
**Lines**: 65-91
**Severity**: HIGH

### Why it happens:

The method attempts to calculate the workload for every student enrolled in a specific course (`$mataKuliahId`).

1.  **Line 67**: `$krsInCourse = Krs::where('mata_kuliah_id', $mataKuliahId)->with('siswa')->get();`
    *   Fetches all KRS records for the specific course AND eager loads the related `siswa`.

2.  **Line 68**: `$siswaIds = $krsInCourse->pluck('siswa_id');`
    *   Extracts student IDs.

3.  **Line 70**: `$courseIdsBySiswa = Krs::whereIn('siswa_id', $siswaIds)->get()->groupBy('siswa_id')->map(fn ($g) => $g->pluck('mata_kuliah_id'));`
    *   **BUG**: This fetches **ALL** KRS records for these students, regardless of semester.
    *   If a student has 8 semesters of history, this loads 8 records per student unnecessarily.
    *   This is an N+1 query because it happens once, but loads excessive data.

4.  **Lines 79-86 (Map Loop)**:
    *   Inside the loop, it accesses `$krs->siswa->nim`.
    *   While `siswa` is eager loaded on line 67, accessing it here creates a tight coupling.
    *   **Logic Error**: The aggregation on line 70 calculates `courseIds` for a student by summing ALL their courses, not just the ones in the *current* semester. This inflates the task count for students taking many courses.

### How I'm gonna fix it:

**Step 1: Identify current semester**
The `$student->semester` is available on the `UserSiswa` model. Use this to filter KRS.

**Step 2: Modify Line 70**
Change from:
```php
$courseIdsBySiswa = Krs::whereIn('siswa_id', $siswaIds)->get()...
```
To:
```php
// Get current semester from the user context or pass it as parameter
$currentSemester = $user?->semester ?? now()->month > 6 ? 1 : 2; // Simplified example
$courseIdsBySiswa = Krs::whereIn('siswa_id', $siswaIds)
    ->where('semester', $currentSemester) // FILTER BY SEMESTER
    ->get()
    ->groupBy('siswa_id')
    ->map(fn ($g) => $g->pluck('mata_kuliah_id'));
```

**Step 3: Add null safety**
Modify line 85:
```php
'nim' => $krs->siswa?->nim ?? '-',
```

---

## 2. Memory Explosion in `BebanCalculator::weeklyLoadDistribution`

**File**: `app/Services/BebanCalculator.php`
**Lines**: 307-330
**Severity**: CRITICAL

### Why it happens:

This method is called by the **Prodi Dashboard** to show load distribution charts. It attempts to calculate the load for **all students in the system**.

1.  **Line 309**:
    ```php
    $students = UserSiswa::with(['krs.mataKuliah.tugas' => function ($q) use ($weekStart, $weekEnd) {
        $q->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()]);
    }])->get();
    ```
    *   **BUG**: This loads **EVERY TASK** in the database matching the date range into memory, attached to every student's relationship.
    *   If there are 1,000 students and 5,000 active tasks for the week, this single line instantiates 5,000 Eloquent models in RAM.

2.  **Lines 320-326 (Loop)**:
    ```php
    foreach ($students as $student) {
        $taskCount = $student->krs
            ->flatMap(fn ($krs) => $krs->mataKuliah?->tugas ?? collect())
            ->count();
        // ...
    }
    ```
    *   **BUG**: This iterates over every student, then over every KRS record for that student, then over every MataKuliah in that KRS.
    *   This is `O(Students * KRS * MataKuliah)` in PHP, which is extremely slow compared to a database `COUNT`.

### How I'm gonna fix it:

**Use SQL Aggregation instead of PHP loops**

Replace the entire method body with a raw query or query builder aggregation:

```php
public static function weeklyLoadDistribution($weekStart, $weekEnd): array
{
    // Use subquery to count tasks per student without loading models
    $distribution = [
        self::LIGHT => 0,
        self::NORMAL => 0,
        self::HEAVY => 0,
        self::OVERLOAD => 0,
    ];

    // Query: Get task count per student via KRS -> Tugas
    $taskCounts = UserSiswa::select('user_siswas.id')
        ->leftJoin('krs', function ($join) {
            $join->on('krs.siswa_id', '=', 'user_siswas.id')
                ->on('krs.semester', '=', 'user_siswas.semester'); // Match current semester
        })
        ->leftJoin('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
        ->leftJoin('tugas', function ($join) use ($weekStart, $weekEnd) {
            $join->on('tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
                ->whereBetween('tugas.deadline', [
                    $weekStart->toDateString(),
                    $weekEnd->toDateString()
                ]);
        })
        ->groupBy('user_siswas.id')
        ->selectRaw('COUNT(tugas.id) as task_count')
        ->pluck('task_count', 'user_siswas.id');

    // Now just iterate over counts, not models
    foreach ($taskCounts as $count) {
        $status = self::forCount($count);
        $distribution[$status]++;
    }

    return $distribution;
}
```

**Key insight**: We use `COUNT(tugas.id)` in SQL which is extremely fast (index scan) and returns integers, not Eloquent models.

---

## 3. Double Query & Logic Bug in `rescheduleSuggestions`

**File**: `app/Services/BebanCalculator.php`
**Lines**: 205-237
**Severity**: HIGH

### Why it happens:

This method suggests alternative dates for a task to avoid overload.

1.  **Line 218**:
    ```php
    $courseIds = Krs::where('siswa_id', $studentId)->pluck('mata_kuliah_id');
    ```
    *   **BUG**: This runs inside a nested loop (lines 217-223) for EVERY student and EVERY day iteration.
    *   This creates **N * 14** queries (where N = number of students in the course).
    *   If a course has 30 students and we check 14 days, this executes **420+ queries** for one task rescheduling.

2.  **Line 219-221**:
    ```php
    $count = Tugas::whereIn('mata_kuliah_id', $courseIds)
        ->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()])
        ->count() + 1;
    ```
    *   **Logic Bug**: It adds `+ 1` to the count, assuming the new task adds to the load. However, the query excludes the *current* task being rescheduled if it already exists in the DB with the old deadline.
    *   If the user is moving a task from Day 1 to Day 5, the query on Day 1 won't see it (good), but the query on Day 5 should see it, but we are adding `+1` blindly.

### How I'm gonna fix it:

**Step 1: Pre-fetch all student course IDs**
Fetch all student course IDs ONCE before the loop.

```php
// Before the for-loop (line 209)
$studentCourseMap = Krs::whereIn('siswa_id', $studentIds)
    ->get()
    ->groupBy('siswa_id')
    ->map(fn ($krsList) => $krsList->pluck('mata_kuliah_id')->toArray());
```

**Step 2: Use pre-fetched data**
Replace line 218:
```php
// Old:
$courseIds = Krs::where('siswa_id', $studentId)->pluck('mata_kuliah_id');

// New:
$courseIds = $studentCourseMap[$studentId] ?? [];
```

**Step 3: Fix the +1 logic**
The `+1` is actually correct IF we treat it as "this task + existing tasks". But we need to handle the case where the task is being moved from one date to another.

Better approach: Just count existing tasks. Remove `+ 1` and let the user decide if the load is acceptable. Or, pass the task ID being moved:
```php
$count = Tugas::whereIn('mata_kuliah_id', $courseIds)
    ->whereBetween('deadline', [...])
    ->where('id', '!=', $excludeTaskId ?? 0) // Exclude current task
    ->count();
```

---

## 4. Redundant Queries in `studentWeeklySummary` and `riskScoreForStudent`

**File**: `app/Services/BebanCalculator.php`
**Lines**: 93-151
**Severity**: MEDIUM

### Why it happens:

These methods are used by the Dosen PA Dashboard to show risk cards.

1.  `studentWeeklySummary` (line 93) calls `riskScoreForStudent` (line 112).
2.  `riskScoreForStudent` (line 116) executes **3 separate database queries**:
    -   Line 118: Get course IDs.
    -   Line 119: Count weekly tasks.
    -   Line 125: Count urgent tasks (3 days).
    -   Lines 128-132: Fetch IPK history.

### How I'm gonna fix it:

**Combine queries into a single pass**

Instead of 3 queries per student, use one aggregated query:

```php
public static function riskScoreForStudent(UserSiswa $student, $weekStart, $weekEnd): int
{
    // Get current semester
    $semester = $student->semester ?? 1;

    // Get ALL data in ONE query using KRS join
    $data = Krs::where('krs.siswa_id', $student->id)
        ->where('krs.semester', $semester)
        ->join('mata_kuliah', 'mata_kuliah.id', '=', 'krs.mata_kuliah_id')
        ->leftJoin('tugas', function ($join) use ($weekStart, $weekEnd) {
            $join->on('tugas.mata_kuliah_id', '=', 'mata_kuliah.id')
                ->whereBetween('tugas.deadline', [
                    $weekStart->toDateString(),
                    $weekEnd->toDateString()
                ]);
        })
        ->selectRaw('
            COUNT(tugas.id) as weekly_task_count,
            SUM(CASE WHEN tugas.deadline >= ? AND tugas.deadline <= ? THEN 1 ELSE 0 END) as urgent_count
        ', [
            now()->startOfDay()->toDateString(),
            now()->addDays(3)->endOfDay()->toDateString()
        ])
        ->first();

    $weeklyTaskCount = (int) ($data->weekly_task_count ?? 0);
    $urgentTaskCount = (int) ($data->urgent_count ?? 0);

    // Get IPK (single query, cached)
    $latestIpk = $student->ipkHistory()->latest('semester')->first();
    // ... rest of IPK logic
}
```

**Also, fix `paRiskCards` to batch:**
Instead of calling `studentWeeklySummary` in a loop, fetch all students at once and map in PHP.

---

## 5. Hardcoded "3 days" in Risk Score Calculation

**File**: `app/Services/BebanCalculator.php`
**Lines**: 126
**Severity**: LOW (Technical Debt)

### How I'm gonna fix it:

Add a class constant:

```php
class BebanCalculator
{
    // ... existing constants
    public const URGENT_TASK_DAYS = 3; // Days to consider a task "urgent"
}
```

Then replace the hardcoded `3`:
```php
->whereBetween('deadline', [now()->startOfDay(), now()->copy()->addDays(self::URGENT_TASK_DAYS)->endOfDay()])
```

---

## 6. Empty Check Missing in `weeklyLoadForCourse`

**File**: `app/Services/BebanCalculator.php`
**Line**: 85
**Severity**: LOW (Potential Error)

### How I'm gonna fix it:

Simple null-safe operator change:

```php
// Old:
'nim' => $krs->siswa->nim ?? '-',

// New:
'nim' => $krs->siswa?->nim ?? '-',
```

---

## 7. `CheckBebanAkademik` Command: Missing Validation

**File**: `app/Console/Commands/CheckBebanAkademik.php`

### How I'm gonna fix it:

**Issue 1: Chunking**
```php
// Replace: foreach ($allSiswa as $siswa)
// With:
UserSiswa::chunk(100, function ($students) use ($overloadThreshold, $collisionThreshold, $collisionWindow) {
    foreach ($students as $siswa) {
        // ... existing logic ...
    }
});
```

**Issue 2: Error Handling**
```php
foreach ($students as $siswa) {
    try {
        // Check SKS overload
        // ...
    } catch (\Exception $e) {
        // Log but continue
        \Log::error("CheckBebanAkademik failed for student {$siswa->id}: " . $e->getMessage());
        continue;
    }
}
```

**Issue 3: Eager Loading**
At the start of the loop iteration:
```php
$siswa->load(['krs.mataKuliah.tugas' => function ($q) use ($collisionWindow) {
    $q->whereBetween('deadline', [now()->toDateString(), now()->addDays($collisionWindow)->toDateString()]);
}]);
```

Or better: Load KRS once per chunk:
```php
$siswaIds = $students->pluck('id');
$krsData = Krs::whereIn('siswa_id', $siswaIds)
    ->with('mataKuliah.tugas')
    ->get()
    ->groupBy('siswa_id');
```

---

## Summary

| Bug ID | Location | Issue | Severity | Fix Strategy |
|--------|----------|-------|----------|--------------|
| 1 | `weeklyLoadForCourse` | N+1 & Logic (All Semesters) | High | Filter KRS by `semester` column |
| 2 | `weeklyLoadDistribution` | Memory Explosion (Loads All Tasks) | Critical | SQL Aggregation (`COUNT`) |
| 3 | `rescheduleSuggestions` | N*14 Queries + Logic Bug | High | Pre-fetch student course IDs |
| 4 | `studentWeeklySummary` | Redundant Queries | Medium | Combine into single SQL query |
| 5 | `riskScoreForStudent` | Hardcoded Magic Numbers | Low | Add class constant |
| 6 | `weeklyLoadForCourse` | Null Safety | Low | Use `?->` operator |
| 7 | `CheckBebanAkademik` | No Chunking / Error Handling | Medium | Add `chunk()`, try-catch, eager load |