@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">{{ $doc->name }}</h1>
            @if($doc->numero_registration)
                <p class="mt-1 text-sm text-zinc-400">N° {{ $doc->numero_registration }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-600 px-4 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour
            </a>
            @hasanyrole('manager|secretaire')
            <a href="{{ route('clients.prestations', $doc) }}" class="inline-flex items-center gap-2 rounded-lg border border-[#967A4B]/50 bg-transparent px-4 py-2.5 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/10">
                Tarifs
            </a>
            @endhasanyrole
            @role('manager')
            <a href="{{ route('clients.edit', $doc) }}" class="inline-flex items-center gap-2 rounded-lg border border-[#967A4B]/50 bg-transparent px-4 py-2.5 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/10">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Modifier
            </a>
            @endrole
        </div>
    </div>

    {{-- Client info --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
        <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Coordonnées</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            @if($doc->phone)
            <div>
                <span class="text-sm text-zinc-500">Téléphone</span>
                <p class="text-zinc-200">{{ $doc->phone }}</p>
            </div>
            @endif
            @if($doc->email)
            <div>
                <span class="text-sm text-zinc-500">Email</span>
                <p class="text-zinc-200">{{ $doc->email }}</p>
            </div>
            @endif
            @if($doc->adresse)
            <div class="sm:col-span-2">
                <span class="text-sm text-zinc-500">Adresse</span>
                <p class="text-zinc-200">{{ $doc->adresse }}</p>
            </div>
            @endif
            @if(!$doc->phone && !$doc->email && !$doc->adresse)
            <p class="text-zinc-500 sm:col-span-2">Aucune coordonnée renseignée.</p>
            @endif
        </div>
    </div>

    {{-- Dashboard stats --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-5">
            <p class="text-sm font-medium text-zinc-400">Commandes (travaux)</p>
            <p class="mt-1 text-2xl font-bold text-[#967A4B]">{{ $travaux->count() }}</p>
        </div>
        @role('manager')
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-5">
            <p class="text-sm font-medium text-zinc-400">Total estimé (DHS)</p>
            <p class="mt-1 text-2xl font-bold text-[#967A4B]">{{ number_format($totalDhs, 0, ',', ' ') }}</p>
        </div>
        @endrole
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-5">
            <p class="text-sm font-medium text-zinc-400">Par statut</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($statutLabels as $value => $label)
                    @php $data = $byStatut[$value] ?? null; @endphp
                    @if($data && $data['count'] > 0)
                        <span class="rounded-full bg-zinc-800 px-2.5 py-1 text-xs text-zinc-300">{{ $label }}: {{ $data['count'] }}</span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Payments summary (manager only) --}}
    @role('manager')
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
        <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Paiements / Montants</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-700">
                        <th class="pb-2 font-medium text-zinc-400">Statut</th>
                        <th class="pb-2 font-medium text-zinc-400 text-right">Nombre</th>
                        <th class="pb-2 font-medium text-zinc-400 text-right">Total (DHS)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statutLabels as $value => $label)
                        @php $data = $byStatut[$value] ?? ['count' => 0, 'total_dhs' => 0]; @endphp
                        <tr class="border-b border-zinc-800/50">
                            <td class="py-2 text-zinc-300">{{ $label }}</td>
                            <td class="py-2 text-right text-zinc-300">{{ $data['count'] }}</td>
                            <td class="py-2 text-right font-medium text-zinc-200">{{ number_format($data['total_dhs'], 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex justify-end border-t border-zinc-700 pt-4">
            <p class="text-lg font-bold text-[#967A4B]">Total général : {{ number_format($totalDhs, 0, ',', ' ') }} DHS</p>
        </div>
    </div>
    @endrole

    {{-- Orders list (travaux) --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
        <div class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-[#967A4B]">Ses commandes (travaux)</h2>
            <a href="{{ route('travaux.create') }}?doc_id={{ $doc->id }}" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-3 py-1.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                Nouveau travail
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/50">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Patient</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Type</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Entrée</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Livraison</th>
                        @role('manager')
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Prix (DHS)</th>
                        @endrole
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($travaux as $t)
                    <tr role="button" tabindex="0" onclick="window.location='{{ route('travaux.show', $t) }}'" onkeydown="if(event.key==='Enter') window.location='{{ route('travaux.show', $t) }}'" class="cursor-pointer border-b border-zinc-800 transition hover:bg-zinc-800/50">
                        <td class="px-4 py-3 text-zinc-200">{{ $t->patient }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $t->type_travail }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $t->date_entree->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $t->date_livraison->format('d/m/Y') }}</td>
                        @role('manager')
                        <td class="px-4 py-3 text-zinc-200">{{ number_format($t->prix_actuel, 0, ',', ' ') }}</td>
                        @endrole
                        <td class="px-4 py-3">
                            @if($t->statut === 'termine')
                                <span class="inline-flex rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-medium text-emerald-300 ring-1 ring-emerald-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'livrer')
                                <span class="inline-flex rounded-full bg-teal-500/15 px-2.5 py-1 text-xs font-medium text-teal-300 ring-1 ring-teal-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'annule')
                                <span class="inline-flex rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-medium text-red-300 ring-1 ring-red-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'a_refaire')
                                <span class="inline-flex rounded-full bg-violet-500/15 px-2.5 py-1 text-xs font-medium text-violet-300 ring-1 ring-violet-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'en_attente')
                                <span class="inline-flex rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-medium text-amber-300 ring-1 ring-amber-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'en_cours')
                                <span class="inline-flex rounded-full bg-blue-500/15 px-2.5 py-1 text-xs font-medium text-blue-300 ring-1 ring-blue-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @elseif($t->statut === 'en_essaiage')
                                <span class="inline-flex rounded-full bg-cyan-500/15 px-2.5 py-1 text-xs font-medium text-cyan-300 ring-1 ring-cyan-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-500/15 px-2.5 py-1 text-xs font-medium text-zinc-300 ring-1 ring-zinc-500/30">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-zinc-500">Aucune commande pour ce client.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
