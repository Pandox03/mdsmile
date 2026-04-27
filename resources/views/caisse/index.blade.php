@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        {{ session('error') }}
    </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Caisse</h1>
            <p class="mt-1 text-sm text-zinc-400">Entrées (encaissements) et sorties (dépenses)</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('caisse.create', ['type' => 'entree']) }}" class="shrink-0 rounded-lg border border-emerald-600 bg-emerald-600/90 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-500">
                + Entrée
            </a>
            <a href="{{ route('caisse.create', ['type' => 'sortie']) }}" class="shrink-0 rounded-lg border border-red-600/80 bg-red-600/90 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-500/90">
                − Sortie
            </a>
        </div>
    </div>

    {{-- Date filter --}}
    <form method="GET" action="{{ route('caisse.index') }}" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-500">Du</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="auth-input rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-500">Au</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="auth-input rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2 text-sm font-medium text-black hover:bg-[#B8986B]">Filtrer</button>
            @if(request()->hasAny(['date_from', 'date_to']))
            <a href="{{ route('caisse.index') }}" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800">Tout</a>
            @endif
            <button type="submit"
                    formaction="{{ route('caisse.report') }}"
                    formtarget="_blank"
                    class="ml-auto rounded-lg border border-[#967A4B]/70 bg-transparent px-4 py-2 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/10">
                Export PDF
            </button>
        </div>
    </form>

    {{-- Summary --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4">
            <p class="text-sm font-medium text-emerald-400">Total entrées (DHS)</p>
            <p class="mt-1 text-2xl font-bold text-emerald-300">{{ number_format($totalEntrees, 0, ',', ' ') }}</p>
        </div>
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4">
            <p class="text-sm font-medium text-red-400">Total sorties (DHS)</p>
            <p class="mt-1 text-2xl font-bold text-red-300">{{ number_format($totalSorties, 0, ',', ' ') }}</p>
        </div>
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <p class="text-sm font-medium text-[#967A4B]">Solde (DHS)</p>
            <p class="mt-1 text-2xl font-bold {{ $solde >= 0 ? 'text-emerald-300' : 'text-red-400' }}">{{ number_format($solde, 0, ',', ' ') }}</p>
        </div>
    </div>

    {{-- Entrées --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
        <h2 class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3 text-lg font-semibold text-[#967A4B]">Entrées (ce que nous encaissons)</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-700 bg-zinc-800/50">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Date</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Libellé / Facture</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] text-right">Montant (DHS)</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] w-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entrees as $e)
                    <tr class="border-b border-zinc-800 hover:bg-zinc-800/50">
                        <td class="px-4 py-3 text-zinc-300">{{ $e->date_mouvement->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            @if($e->facture)
                                <a href="{{ route('factures.show', $e->facture) }}" class="text-[#967A4B] hover:underline">{{ $e->facture->numero }}</a>
                                @if($e->description) <span class="text-zinc-500">— {{ Str::limit($e->description, 40) }}</span> @endif
                            @else
                                <span class="text-zinc-200">{{ $e->description ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-emerald-400">+ {{ number_format($e->montant, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('caisse.edit', $e) }}" class="rounded p-1.5 text-[#967A4B] hover:bg-[#967A4B]/10" title="Modifier"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                @hasanyrole('manager|secretaire')
                                <form method="POST" action="{{ route('caisse.destroy', $e) }}" class="inline" onsubmit="return confirm('Supprimer cette entrée ?');">@csrf @method('DELETE')<button type="submit" class="rounded p-1.5 text-red-400 hover:bg-red-500/10" title="Supprimer"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                                @endhasanyrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-zinc-500">Aucune entrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sorties --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
        <h2 class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3 text-lg font-semibold text-[#967A4B]">Sorties (ce que nous dépensons)</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-700 bg-zinc-800/50">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Date</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Libellé</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] text-right">Montant (DHS)</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] w-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sorties as $s)
                    <tr class="border-b border-zinc-800 hover:bg-zinc-800/50">
                        <td class="px-4 py-3 text-zinc-300">{{ $s->date_mouvement->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-zinc-200">{{ $s->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-red-400">− {{ number_format($s->montant, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('caisse.edit', $s) }}" class="rounded p-1.5 text-[#967A4B] hover:bg-[#967A4B]/10" title="Modifier"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                @hasanyrole('manager|secretaire')
                                <form method="POST" action="{{ route('caisse.destroy', $s) }}" class="inline" onsubmit="return confirm('Supprimer cette sortie ?');">@csrf @method('DELETE')<button type="submit" class="rounded p-1.5 text-red-400 hover:bg-red-500/10" title="Supprimer"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                                @endhasanyrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-zinc-500">Aucune sortie.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
