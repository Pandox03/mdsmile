@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">{{ $type === 'entree' ? 'Nouvelle entrée en caisse' : 'Nouvelle sortie de caisse' }}</h1>
        <p class="mt-1 text-sm text-zinc-400">{{ $type === 'entree' ? 'Encaissement (ce que nous gagnons)' : 'Dépense (ce que nous sortons)' }}</p>
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

    <form method="POST" action="{{ route('caisse.store') }}" class="space-y-8">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Montant (DHS) <span class="text-red-400">*</span></label>
                    <input type="number" name="montant" value="{{ old('montant') }}" step="0.01" min="0.01" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="0">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Date <span class="text-red-400">*</span></label>
                    <input type="date" name="date_mouvement" value="{{ old('date_mouvement', now()->format('Y-m-d')) }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                @if($type === 'entree')
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Facture (optionnel)</label>
                    <select name="facture_id" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                        <option value="">— Aucune —</option>
                        @foreach($factures as $f)
                            <option value="{{ $f->id }}" {{ old('facture_id') == $f->id ? 'selected' : '' }}>{{ $f->numero }} ({{ $f->date_facture->format('d/m/Y') }}) — {{ $f->doc->name ?? '—' }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Libellé / Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="{{ $type === 'entree' ? 'Ex. Paiement client' : 'Ex. Achat fournitures' }}">
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Enregistrer</button>
            <a href="{{ route('caisse.index') }}" class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
