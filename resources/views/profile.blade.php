@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Mon profil</h1>
        <p class="mt-1 text-sm text-zinc-400">Informations du compte, mot de passe et suppression</p>
    </div>

    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
        <livewire:profile.update-profile-information-form />
    </div>

    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
        <livewire:profile.update-password-form />
    </div>

    <div class="rounded-xl border border-red-500/20 bg-zinc-900/80 p-6">
        <livewire:profile.delete-user-form />
    </div>
</div>
@endsection
