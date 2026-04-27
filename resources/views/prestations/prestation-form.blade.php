@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    @if($errors->any())
    <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-400">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">{{ $prestation ? 'Modifier la prestation' : 'Nouvelle prestation' }}</h1>
        <p class="mt-1 text-sm text-zinc-400">Laissez le prix vide pour « Sur devis ».</p>
    </div>

    <form method="POST" action="{{ $prestation ? route('prestations.update', $prestation) : route('prestations.store') }}" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
        @csrf
        @if($prestation) @method('PUT') @endif
        <div class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-400">Catégorie</label>
                <select name="prestation_category_id" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (int) old('prestation_category_id', $selectedCategoryId) === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-400">Nom de la prestation</label>
                <input type="text" name="name" value="{{ old('name', $prestation?->name) }}" required class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="Ex. Couronne Céramo Métallique">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-400">Prix (DH)</label>
                <input type="number" name="price" value="{{ old('price', $prestation?->price) }}" min="0" step="0.01" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="Vide = Sur devis">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-400">Ordre d'affichage</label>
                <input type="number" name="order" value="{{ old('order', $prestation?->order ?? 0) }}" min="0" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Enregistrer</button>
            <a href="{{ route('prestations.index') }}" class="rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm font-medium text-zinc-300 hover:bg-zinc-700">Annuler</a>
        </div>
    </form>
</div>
@endsection
