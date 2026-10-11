@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Devis Invisalign</h1>
            <p class="mt-1 text-sm text-zinc-500">Créer, modifier et télécharger les devis type aligners</p>
        </div>
        <a href="{{ route('devis-invisalign.create') }}" class="shrink-0 rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-center text-sm font-medium text-black transition hover:bg-[#B8986B]">
            Nouveau devis
        </a>
    </div>

    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <form method="GET" action="{{ route('devis-invisalign.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px]">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Client (fiche)</label>
                <select name="doc_id" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    <option value="">— Tous —</option>
                    @foreach($docs as $d)
                    <option value="{{ $d->id }}" {{ request('doc_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[200px] flex-1">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="N°, client, patient…" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">Filtrer</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-[#967A4B]/20 bg-zinc-900/80">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-700 text-zinc-400">
                    <th class="px-4 py-3 font-medium">N°</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Client</th>
                    <th class="px-4 py-3 font-medium">Patient</th>
                    <th class="px-4 py-3 font-medium text-right">Total HT</th>
                    <th class="px-4 py-3 font-medium text-right">Payé</th>
                    <th class="px-4 py-3 font-medium text-right">Reste</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devisList as $d)
                <tr class="border-b border-zinc-800 text-zinc-200">
                    <td class="px-4 py-3 font-mono text-[#967A4B]">{{ $d->numero }}</td>
                    <td class="px-4 py-3 text-zinc-400">{{ $d->date_devis?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ $d->client_nom }}</td>
                    <td class="px-4 py-3">{{ $d->patient_nom }}</td>
                    @php
                        $paye = (float) ($d->paiements_sum_montant ?? 0);
                        $reste = round((float) $d->total_ttc - $paye, 2);
                    @endphp
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $d->total_ttc, 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($paye, 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($reste, 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-wrap justify-end gap-2">
                            <a href="{{ route('devis-invisalign.pdf', $d) }}" target="_blank" class="rounded border border-zinc-600 px-2 py-1 text-xs text-zinc-300 hover:border-[#967A4B] hover:text-[#967A4B]">PDF</a>
                            <a href="{{ route('devis-invisalign.edit', $d) }}" class="rounded border border-zinc-600 px-2 py-1 text-xs text-zinc-300 hover:border-[#967A4B] hover:text-[#967A4B]">Modifier</a>
                            <form method="POST" action="{{ route('devis-invisalign.destroy', $d) }}" class="inline" onsubmit="return confirm('Supprimer ce devis ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded border border-red-900/50 px-2 py-1 text-xs text-red-400 hover:bg-red-950/40">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-zinc-500">Aucun devis. Créez-en un avec « Nouveau devis ».</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($devisList->hasPages())
    <div class="flex justify-center">{{ $devisList->links() }}</div>
    @endif
</div>
@endsection
