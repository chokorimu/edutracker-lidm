<?php

use App\Models\UserAdmin;
use App\Models\UserDosen;
use App\Models\UserProdi;
use App\Models\UserSiswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

new class extends Component
{
    public string $email = '';

    public string $password = '';

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

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

        $this->addError('email', 'Email atau password salah.');
    }

    private function attemptGuard(string $guard, string $modelClass, string $route, array $credentials)
    {
        if ($this->repairLegacyPlaintextPassword($modelClass, $credentials) === false) {
            return null;
        }

        if (! Auth::guard($guard)->attempt($credentials)) {
            return null;
        }

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
}; ?>

<div class="min-h-screen flex flex-col justify-between bg-[#FBFBF9] text-[#1D1D1F] antialiased selection:bg-[#1D1D1F] selection:text-white font-sans">

    <header class="w-full bg-[#FBFBF9]/80 backdrop-blur-xl border-b border-[#E8E8ED] px-6 md:px-16 py-5 flex justify-between items-center z-50">
        <a href="/" class="text-xl font-bold tracking-tighter lowercase select-none hover:opacity-80 transition-opacity">edutrack daily</a>
        <span class="text-xs font-bold uppercase tracking-widest text-[#86868B]">Portal Autentikasi</span>
    </header>

    <main class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-[420px] bg-white border border-[#E8E8ED] p-8 sm:p-10 rounded-[28px] shadow-[0_10px_30px_rgba(0,0,0,0.02)]">
            
            <div class="text-center mb-8">
                <span class="text-[11px] font-bold tracking-widest uppercase text-[#86868B] block mb-1">Sistem SKS Terpadu</span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#1D1D1F]">Masuk ke akun.</h1>
            </div>

            <form wire:submit="login" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold tracking-wider uppercase text-[#86868B] mb-1.5">Email</label>
                    <input
                        type="email"
                        wire:model="email"
                        class="w-full bg-[#F5F5F7] border border-transparent focus:border-[#1D1D1F] focus:bg-white rounded-xl px-4 py-3.5 text-sm text-[#1D1D1F] placeholder-[#86868B] outline-none transition-all duration-200"
                        placeholder="nama@kampus.ac.id"
                        autofocus
                    >
                    @error('email') 
                        <span class="text-[#FF3B30] text-xs font-medium mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold tracking-wider uppercase text-[#86868B] mb-1.5">Kata Sandi</label>
                    <input
                        type="password"
                        wire:model="password"
                        class="w-full bg-[#F5F5F7] border border-transparent focus:border-[#1D1D1F] focus:bg-white rounded-xl px-4 py-3.5 text-sm text-[#1D1D1F] placeholder-[#86868B] outline-none transition-all duration-200"
                        placeholder="••••••••••••"
                    >
                    @error('password') 
                        <span class="text-[#FF3B30] text-xs font-medium mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full bg-[#1D1D1F] text-white py-3.5 rounded-xl font-medium text-sm tracking-tight hover:bg-black active:scale-[0.98] disabled:opacity-50 transition-all duration-200 shadow-sm flex items-center justify-center gap-2 group"
                    >
                        <span wire:loading.remove>Masuk Sekarang</span>
                        <span wire:loading>Memverifikasi...</span>
                        <svg wire:loading.remove class="w-4 h-4 text-white/70 group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

        </div>
    </main>

    <footer class="py-6 text-center text-xs text-[#86868B] select-none">
        Universitas Negeri Malang &bull; Dilindungi Hak Cipta.
    </footer>

</div>
