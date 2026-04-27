@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Détails internes (non comptabilisés)</h1>
            <p class="mt-1 text-sm text-zinc-400">Montant total, comptabilisé et non comptabilisé par travail</p>
        </div>
        <a href="{{ route('factures.index') }}" class="shrink-0 rounded-lg border border-zinc-600 px-5 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">
            Retour aux factures
        </a>
    </div>

    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <form method="GET" action="{{ route('factures.internes.index') }}" class="flex flex-wrap items-center gap-4">
            <input type="text" name="recherche" value="{{ request('recherche') }}" placeholder="Travail, patient, type..." class="auth-input min-w-[180px] rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 px-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            <select name="doc_id" class="auth-input min-w-[200px] rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                <option value="">Tous les clients</option>
                @foreach($docs as $d)
                    <option value="{{ $d->id }}" {{ request('doc_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                Filtrer
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#967A4B]/20 bg-zinc-900/80">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Travail</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Client</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] text-right">Montant total (DHS)</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] text-right">Comptabilisé (DHS)</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] text-right">Non comptabilisé (DHS)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($travaux as $t)
                    <tr class="border-b border-zinc-800 transition hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <span class="font-medium text-zinc-200">{{ $t->reference }}</span>
                            @if($t->patient || $t->type_travail)
                                <span class="block text-xs text-zinc-500">{{ $t->patient }}{{ $t->patient && $t->type_travail ? ' · ' : '' }}{{ $t->type_travail }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-300">{{ $t->doc->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-zinc-300">{{ number_format($t->prix_dhs, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-zinc-300">{{ number_format($t->montant_comptabilise_cap, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $t->montant_non_comptabilise > 0 ? 'text-red-400' : 'text-zinc-500' }}">{{ number_format($t->montant_non_comptabilise, 0, ',', ' ') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">Aucun travail.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($travaux->hasPages())
        <div class="flex items-center justify-center gap-2 border-t border-zinc-800 px-4 py-3">
            <span class="text-sm text-zinc-400">Page {{ $travaux->currentPage() }} sur {{ $travaux->lastPage() }}</span>
            <div class="flex gap-1">
                @if($travaux->onFirstPage())
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></span>
                @else
                    <a href="{{ $travaux->previousPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
                @endif
                @if($travaux->hasMorePages())
                    <a href="{{ $travaux->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
