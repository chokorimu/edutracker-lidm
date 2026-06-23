<?php

namespace Tests\Feature;

use App\Models\UserAdmin;
use App\Models\UserDosen;
use App\Models\UserSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login(): void
    {
        $admin = UserAdmin::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.test',
            'password' => 'secret123',
        ]);

        $this->withSession([]);

        Livewire::test('pages::auth.login')
            ->set('email', $admin->email)
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(Auth::guard('admin')->check());
    }

    public function test_dosen_can_login(): void
    {
        $dosen = UserDosen::create([
            'name' => 'Dosen Test',
            'email' => 'dosen@example.test',
            'password' => 'secret123',
            'nidn' => 'NIDN-001',
            'fakultas' => 'Teknik',
        ]);

        $this->withSession([]);

        Livewire::test('pages::auth.login')
            ->set('email', $dosen->email)
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect(route('dosen.dashboard'));

        $this->assertTrue(Auth::guard('dosen')->check());
    }

    public function test_siswa_can_login(): void
    {
        $siswa = UserSiswa::create([
            'name' => 'Siswa Test',
            'email' => 'siswa@example.test',
            'password' => 'secret123',
            'nim' => '220101999',
            'prodi' => 'Informatika',
            'semester' => 4,
        ]);

        $this->withSession([]);

        Livewire::test('pages::auth.login')
            ->set('email', $siswa->email)
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect(route('siswa.dashboard'));

        $this->assertTrue(Auth::guard('siswa')->check());
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->withSession([]);

        Livewire::test('pages::auth.login')
            ->set('email', 'missing@example.test')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertFalse(Auth::guard('admin')->check());
        $this->assertFalse(Auth::guard('dosen')->check());
        $this->assertFalse(Auth::guard('siswa')->check());
    }
}
