@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Dashboard Dosen</h1>
            <form method="POST" action="{{ route('dosen.logout') }}">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Logout
                </button>
            </form>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p>Halo, {{ $user->name }} 👋</p>
            <p class="text-gray-500 text-sm mt-2">Dashboard dosen masih placeholder.</p>
        </div>
    </div>
</div>
@endsection
