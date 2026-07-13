<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserAdmin;
use App\Models\UserDosen;
use App\Models\UserProdi;
use App\Models\UserSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if ($redirect = $this->attemptGuard('admin', UserAdmin::class, 'admin.dashboard', $credentials)) {
            return $redirect;
        }

        if ($redirect = $this->attemptGuard('dosen', UserDosen::class, 'dosen.dashboard', $credentials)) {
            return $redirect;
        }

        if ($redirect = $this->attemptGuard('siswa', UserSiswa::class, 'siswa.dashboard', $credentials)) {
            return $redirect;
        }

        if ($redirect = $this->attemptGuard('prodi', UserProdi::class, 'prodi.dashboard', $credentials)) {
            return $redirect;
        }

        throw ValidationException::withMessages([
            'email' => ['Email atau password salah.'],
        ]);
    }

    private function attemptGuard(string $guard, string $modelClass, string $route, array $credentials)
    {
        if ($this->repairLegacyPlaintextPassword($modelClass, $credentials) === false) {
            return null;
        }

        if (! Auth::guard($guard)->attempt($credentials)) {
            return null;
        }

        $request = request();
        $request->session()->regenerate();

        return redirect()->route($route);
    }

    private function repairLegacyPlaintextPassword(string $modelClass, array $credentials): ?bool
    {
        $user = $modelClass::where('email', $credentials['email'])->first();

        if (! $user || blank($user->password)) {
            return null;
        }

        if ((Hash::info($user->password)['algoName'] ?? 'unknown') !== 'unknown') {
            return null;
        }

        if (! hash_equals((string) $user->password, (string) $credentials['password'])) {
            return false;
        }

        $user->forceFill([
            'password' => Hash::make($credentials['password']),
        ])->save();

        return true;
    }
}
