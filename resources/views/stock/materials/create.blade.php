@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Ajouter un matériau</h1>
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

    <form method="POST" action="{{ route('stock.materials.store') }}" class="space-y-8">
        @csrf
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Informations du matériau</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="Ex. Cire dentaire">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Référence</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Quantité</label>
                    <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0" step="any" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Seuil minimum d'alerte</label>
                    <input type="number" name="seuil_alerte_min" value="{{ old('seuil_alerte_min', 5) }}" min="0" step="any" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Unité</label>
                    <select name="unite" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                        <option value="pce" {{ old('unite', 'pce') === 'pce' ? 'selected' : '' }}>pce</option>
                        <option value="g" {{ old('unite') === 'g' ? 'selected' : '' }}>g</option>
                        <option value="ml" {{ old('unite') === 'ml' ? 'selected' : '' }}>ml</option>
                        <option value="kg" {{ old('unite') === 'kg' ? 'selected' : '' }}>kg</option>
                        <option value="L" {{ old('unite') === 'L' ? 'selected' : '' }}>L</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Fournisseur</label>
                    <select name="fournisseur_id" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                        <option value="">— Aucun —</option>
                        @foreach($fournisseurs as $f)
                            <option value="{{ $f->id }}" {{ old('fournisseur_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Ajouter le matériau</button>
            <a href="{{ route('stock.index') }}" class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
