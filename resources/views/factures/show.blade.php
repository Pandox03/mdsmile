@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Facture {{ $facture->numero }}</h1>
            <p class="mt-1 text-sm text-zinc-400">{{ $facture->date_facture->format('d/m/Y') }} — {{ $facture->doc->name }}</p>
            <p class="mt-2 text-xs text-zinc-500">Facture liée aux travaux, sans statut de paiement.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('factures.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-600 px-4 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Liste des factures
            </a>
            <a href="{{ route('factures.pdf', $facture) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-[#967A4B]/50 bg-transparent px-4 py-2.5 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/10">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Télécharger PDF
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6 print:border-zinc-600">
        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-sm text-zinc-500">Client</p>
                <p class="font-medium text-zinc-200">{{ $facture->doc->name }}</p>
                @if($facture->doc->numero_registration)
                    <p class="text-sm text-zinc-400">N° {{ $facture->doc->numero_registration }}</p>
                @endif
                @if($facture->doc->adresse)
                    <p class="mt-1 text-sm text-zinc-400">{{ $facture->doc->adresse }}</p>
                @endif
            </div>
            <div class="text-left sm:text-right">
                <p class="text-sm text-zinc-500">Facture n°</p>
                <p class="font-bold text-[#967A4B]">{{ $facture->numero }}</p>
                <p class="text-sm text-zinc-400">Date : {{ $facture->date_facture->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-end justify-end gap-6 border-t border-zinc-700 pt-4">
            <div class="text-right">
                <p class="text-sm text-zinc-500">Total facture</p>
                <p class="text-lg font-bold text-zinc-200">{{ number_format($facture->total_facture, 0, ',', ' ') }} DHS</p>
            </div>
        </div>
    </div>
</div>
@endsection
