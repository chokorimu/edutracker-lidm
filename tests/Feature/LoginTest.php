<?php

namespace Tests\Feature;

use App\Models\UserAdmin;
use App\Models\UserDosen;
use App\Models\UserProdi;
use App\Models\UserSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login(): void
    {
        $admin = UserAdmin::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.test',
            'password' => Hash::make('secret123'),
        ]);

        $this->withSession([]);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(Auth::guard('admin')->check());
    }

    public function test_dosen_can_login(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Test',
            'email' => 'dosen@example.test',
            'password' => Hash::make('secret123'),
            'nidn' => 'NIDN-001',
            'fakultas' => 'Teknik',
        ]);

        $this->withSession([]);

        $this->post(route('login'), [
            'email' => $dosen->email,
            'password' => 'secret123',
        ])->assertRedirect(route('dosen.dashboard'));

        $this->assertTrue(Auth::guard('dosen')->check());
    }

    public function test_dosen_login_repairs_legacy_plaintext_password(): void
    {
        DB::table('user_dosens')->insert([
            'name' => 'Dosen Legacy',
            'email' => 'legacy-dosen@example.test',
            'password' => 'password123',
            'nidn' => 'NIDN-LEGACY',
            'fakultas' => 'Teknik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([]);

        $this->post(route('login'), [
            'email' => 'legacy-dosen@example.test',
            'password' => 'password123',
        ])->assertRedirect(route('dosen.dashboard'));

        $password = UserDosen::where('email', 'legacy-dosen@example.test')->value('password');

        $this->assertTrue(Auth::guard('dosen')->check());
        $this->assertTrue(Hash::check('password123', $password));
        $this->assertNotSame('password123', $password);
    }

    public function test_siswa_can_login(): void
    {
        $siswa = UserSiswa::create([
            'name' => 'Siswa Test',
            'email' => 'siswa@example.test',
            'password' => Hash::make('secret123'),
            'nim' => '220101999',
            'prodi' => 'Informatika',
            'semester' => 4,
            'profile_completed' => true,
        ]);

        $this->withSession([]);

        $this->post(route('login'), [
            'email' => $siswa->email,
            'password' => 'secret123',
        ])->assertRedirect(route('siswa.dashboard'));

        $this->assertTrue(Auth::guard('siswa')->check());
    }

    public function test_prodi_can_login(): void
    {
        $prodi = UserProdi::create([
            'name' => 'Prodi Test',
            'email' => 'prodi@example.test',
            'password' => Hash::make('secret123'),
        ]);

        $this->withSession([]);

        $this->post(route('login'), [
            'email' => $prodi->email,
            'password' => 'secret123',
        ])->assertRedirect(route('prodi.dashboard'));

        $this->assertTrue(Auth::guard('prodi')->check());
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->withSession([]);

        $this->post(route('login'), [
            'email' => 'missing@example.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email']);

        $this->assertFalse(Auth::guard('admin')->check());
        $this->assertFalse(Auth::guard('dosen')->check());
        $this->assertFalse(Auth::guard('siswa')->check());
    }
}
