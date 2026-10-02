@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-[#967A4B]">Modifier {{ $bon->numero }}</h1>
            <p class="mt-1 text-sm text-zinc-500">Bon de livraison</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('bons-livraison.pdf', $bon) }}" target="_blank" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm text-zinc-300 hover:border-[#967A4B] hover:text-[#967A4B]">PDF</a>
            <a href="{{ route('bons-livraison.index') }}" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm text-zinc-300 hover:border-[#967A4B] hover:text-[#967A4B]">Liste</a>
        </div>
    </div>

    @if ($errors->any())
    <div class="rounded-lg border border-red-500/40 bg-red-950/40 px-4 py-3 text-sm text-red-200">
        <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('bons-livraison.update', $bon) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('bons-livraison.partials.form', [
            'docs' => $docs,
            'travaux' => $travaux,
            'bon' => $bon,
            'paiementLabels' => $paiementLabels,
            'typePaiementLabels' => $typePaiementLabels,
        ])
        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('bons-livraison.index') }}" class="rounded-lg border border-zinc-600 px-5 py-2.5 text-sm text-zinc-300">Retour</a>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
