@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Modifier le client</h1>
    </div>

    @if($errors->any())
    <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-400">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('clients.update', $doc) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Informations du client</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $doc->name) }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="Nom du dentiste">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">N° d'enregistrement</label>
                    <input type="text" name="numero_registration" value="{{ old('numero_registration', $doc->numero_registration) }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $doc->phone) }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Email</label>
                    <input type="email" name="email" value="{{ old('email', $doc->email) }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Adresse</label>
                    <textarea name="adresse" rows="3" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">{{ old('adresse', $doc->adresse) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                Enregistrer
            </button>
            <a href="{{ route('clients.index') }}" class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
