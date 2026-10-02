@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Bons de livraison</h1>
            <p class="mt-1 text-sm text-zinc-500">Dentiste, patient, détail du travail et statut de paiement</p>
        </div>
        <a href="{{ route('bons-livraison.create') }}" class="shrink-0 rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-center text-sm font-medium text-black hover:bg-[#B8986B]">Nouveau bon</a>
    </div>

    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <form method="GET" action="{{ route('bons-livraison.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[180px]">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Dentiste</label>
                <select name="doc_id" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
                    <option value="">— Tous —</option>
                    @foreach($docs as $d)
                    <option value="{{ $d->id }}" {{ request('doc_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Paiement</label>
                <select name="paiement_statut" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100">
                    <option value="">— Tous —</option>
                    @foreach($paiementLabels as $value => $label)
                    <option value="{{ $value }}" {{ request('paiement_statut') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[200px] flex-1">
                <label class="mb-1 block text-xs font-medium text-zinc-500">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="N°, patient, dentiste…" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 text-sm text-zinc-100">
            </div>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Filtrer</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-[#967A4B]/20 bg-zinc-900/80">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-700 text-zinc-400">
                    <th class="px-4 py-3 font-medium">N°</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Dentiste</th>
                    <th class="px-4 py-3 font-medium">Patient</th>
                    <th class="px-4 py-3 font-medium">Détail</th>
                    <th class="px-4 py-3 font-medium">Paiement</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bons as $b)
                <tr class="border-b border-zinc-800 text-zinc-200">
                    <td class="px-4 py-3 font-mono text-[#967A4B]">{{ $b->numero }}</td>
                    <td class="px-4 py-3 text-zinc-400">{{ $b->date_livraison?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ $b->dentiste_nom }}</td>
                    <td class="px-4 py-3">{{ $b->patient_nom }}</td>
                    <td class="max-w-[220px] truncate px-4 py-3 text-zinc-400" title="{{ $b->details_travail }}">{{ \Illuminate\Support\Str::limit($b->details_travail, 60) }}</td>
                    <td class="px-4 py-3">
                        @php
                            $badge = match($b->paiement_statut) {
                                'paye' => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
                                'partiel' => 'border-amber-500/40 bg-amber-500/10 text-amber-300',
                                default => 'border-red-500/40 bg-red-500/10 text-red-300',
                            };
                        @endphp
                        <span class="rounded border px-2 py-0.5 text-xs {{ $badge }}">{{ $paiementLabels[$b->paiement_statut] ?? $b->paiement_statut }}</span>
                    </td>
                    <td class="px-4 py-3 text-zinc-400">{{ $typePaiementLabels[$b->type_paiement] ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-wrap justify-end gap-2">
                            <a href="{{ route('bons-livraison.pdf', $b) }}" target="_blank" class="rounded border border-zinc-600 px-2 py-1 text-xs text-zinc-300 hover:border-[#967A4B] hover:text-[#967A4B]">PDF</a>
                            <a href="{{ route('bons-livraison.edit', $b) }}" class="rounded border border-zinc-600 px-2 py-1 text-xs text-zinc-300 hover:border-[#967A4B] hover:text-[#967A4B]">Modifier</a>
                            <form method="POST" action="{{ route('bons-livraison.destroy', $b) }}" class="inline" onsubmit="return confirm('Supprimer ce bon ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded border border-red-900/50 px-2 py-1 text-xs text-red-400">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-zinc-500">Aucun bon de livraison.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bons->hasPages())
    <div class="flex justify-center">{{ $bons->links() }}</div>
    @endif
</div>
@endsection
