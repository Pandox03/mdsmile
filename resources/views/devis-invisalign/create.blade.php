@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-[#967A4B]">Nouveau devis Invisalign</h1>
            <p class="mt-1 text-sm text-zinc-500">Remplissez le devis comme sur le modèle papier, puis enregistrez.</p>
        </div>
        <a href="{{ route('devis-invisalign.index') }}" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm text-zinc-300 hover:border-[#967A4B] hover:text-[#967A4B]">← Liste</a>
    </div>

    @if ($errors->any())
    <div class="rounded-lg border border-red-500/40 bg-red-950/40 px-4 py-3 text-sm text-red-200">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('devis-invisalign.store') }}" class="space-y-6">
        @csrf
        @include('devis-invisalign.partials.form', ['docs' => $docs, 'previewNumero' => $previewNumero, 'defaults' => $defaults, 'selectedDocId' => $selectedDocId ?? null])
        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('devis-invisalign.index') }}" class="rounded-lg border border-zinc-600 px-5 py-2.5 text-sm text-zinc-300 hover:bg-zinc-800">Annuler</a>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">Enregistrer le devis</button>
        </div>
    </form>
</div>
@endsection
