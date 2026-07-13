@props(['role' => ''])
<div class="flex items-center gap-2">
    <img class="h-8 w-auto object-contain" src="{{ asset('images/logo.png') }}" alt="SiEdu Logo">
    <div class="flex-col">
        <p class="text-sky-700 text-xl font-bold tracking-tight font-display select-none">SIEDU</p>
        @if($role)
            <p class="text-xs font-bold uppercase tracking-widest">{{ $role }}</p>
        @endif
    </div>
</div>