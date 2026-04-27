@extends('layouts.dashboard')

@push('styles')
<style>
@page {
    margin: 1cm;
    size: auto;
}
@media print {
    html, body { margin: 0 !important; padding: 0 !important; }
    /* Hide header, sidebar, bottom nav, and action buttons when printing Fiche Détail Travail */
    header.sticky,
    aside.hidden.w-64,
    nav.flex.justify-around.border-t,
    #fiche-action-buttons,
    .no-print { display: none !important; }

    /* Odontogram: lock coordinates so tooth numbers (1–32) don't drift during print */
    #odontogram-print-container {
        position: relative !important;
        width: 100% !important;
        max-width: 42rem !important;
    }
    .odontogram-numbers-overlay {
        position: absolute !important;
    }
    .odontogram-numbers-overlay > div {
        position: absolute !important;
        inset: 0 !important;
    }
    .odontogram-tooth-number {
        position: absolute !important;
        transform: translate(-50%, -50%) !important;
    }
    /* Tooth fill colors and material labels must remain visible in print */
    .odontogram-wrapper,
    .odontogram-wrapper svg,
    .odontogram-wrapper svg * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endpush

@push('vite')
@vite(['resources/js/odontogram/show.jsx'])
@endpush

@php
$OURS_TO_FDI = [
    1 => 11, 2 => 12, 3 => 13, 4 => 14, 5 => 15, 6 => 16, 7 => 17, 8 => 18,
    9 => 21, 10 => 22, 11 => 23, 12 => 24, 13 => 25, 14 => 26, 15 => 27, 16 => 28,
    17 => 31, 18 => 32, 19 => 33, 20 => 34, 21 => 35, 22 => 36, 23 => 37, 24 => 38,
    25 => 41, 26 => 42, 27 => 43, 28 => 44, 29 => 45, 30 => 46, 31 => 47, 32 => 48,
];
$teethList = $travail->teeth->pluck('tooth_number')->map(fn ($n) => $OURS_TO_FDI[$n] ?? $n)->sort()->values()->toArray();
$teethListStr = implode(', ', $teethList);
$concernedSet = $travail->teeth->pluck('tooth_number')->flip()->toArray();
$MATERIAL_COLORS = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4', '#84cc16'];
$materialGroups = [];
$colorIndex = 0;
foreach ($travail->teeth->filter(fn ($t) => $t->stock_id !== null)->groupBy('stock_id') as $key => $items) {
    $first = $items->first();
    $name = $first->stock?->name ?? '—';
    $materialGroups[] = ['name' => $name, 'color' => $MATERIAL_COLORS[$colorIndex % count($MATERIAL_COLORS)]];
    $colorIndex++;
}
$phases = $travail->teeth->sortBy('phase')->groupBy('phase');
$phaseGroups = [];
foreach ($phases as $phaseNum => $teethInPhase) {
    $prestInPhase = [];
    foreach ($teethInPhase->filter(fn ($t) => $t->prestation_id !== null)->groupBy('prestation_id') as $prestationId => $items) {
        $first = $items->first();
        $prestInPhase[] = [
            'name' => $first->prestation?->name ?? '—',
            'teeth' => $items->pluck('tooth_number')->map(fn ($n) => $OURS_TO_FDI[$n] ?? $n)->sort()->values()->toArray(),
        ];
    }
    $phaseGroups[$phaseNum] = $prestInPhase;
}
$prestationGroups = $travail->teeth->filter(fn ($t) => $t->prestation_id !== null)->groupBy('prestation_id')->map(function ($items) use ($OURS_TO_FDI) {
    $first = $items->first();
    return ['name' => $first->prestation?->name ?? '—', 'teeth' => $items->pluck('tooth_number')->map(fn ($n) => $OURS_TO_FDI[$n] ?? $n)->sort()->values()->toArray()];
})->values()->all();
@endphp

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-[#967A4B]">Fiche Détail Travail</h1>
        @hasanyrole('manager|secretaire|assistante|cadcam')
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('travaux.updateStatut', $travail) }}" class="flex items-center gap-2 no-print">
                @csrf
                @method('PATCH')
                <label for="statut-select" class="text-sm font-medium text-zinc-400">Statut</label>
                <select name="statut" id="statut-select" onchange="this.form.submit()" class="rounded-lg border border-[#967A4B]/50 bg-zinc-800 px-3 py-2 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    @foreach($statutLabels as $value => $label)
                        <option value="{{ $value }}" {{ $travail->statut === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @endhasanyrole
    </div>

    {{-- Main card --}}
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/90 p-6 shadow-lg">
        <div class="space-y-6">
            {{-- Dentist & Patient --}}
            <div class="grid gap-6 border-b border-[#967A4B]/20 pb-6 sm:grid-cols-2">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#967A4B]/20 text-[#967A4B]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#967A4B]">Dentiste</p>
                        <p class="text-zinc-200">Dr. {{ $travail->doc?->name ?? $travail->dentiste }}</p>
                        <p class="text-sm text-zinc-400">{{ $travail->doc?->adresse ?? '—' }}</p>
                        <p class="text-sm text-zinc-400">Tél: {{ $travail->doc?->phone ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#967A4B]/20 text-[#967A4B]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#967A4B]">Patient</p>
                        <p class="text-zinc-200">{{ $travail->patient }}</p>
                        @if($travail->numero_fiche)
                        <p class="text-sm text-zinc-400">N° fiche: {{ $travail->numero_fiche }}</p>
                        @endif
                        <p class="text-sm text-zinc-400">ID: {{ $travail->id }}</p>
                        <p class="text-sm text-zinc-400">Âge: {{ $travail->patient_age ? $travail->patient_age . ' ans' : '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Prosthesis info --}}
            <div class="border-b border-[#967A4B]/20 pb-6">
                <h3 class="mb-3 text-sm font-semibold text-[#967A4B]">Information sur la Prothèse</h3>
                <p class="text-zinc-200"><span class="text-zinc-400">Prestation:</span> {{ $travail->type_travail_display }}</p>
                <p class="mt-1 text-zinc-200"><span class="text-zinc-400">Dents concernées:</span> {{ $teethListStr ?: '—' }}</p>
                @if(count($travail->teeth) > 0)
                @php
                $teethData = $travail->teeth->map(fn ($t) => [
                    'tooth_number' => $t->tooth_number,
                    'stock_id' => $t->stock_id,
                    'stock_name' => $t->stock?->name ?? ($t->prestation?->name ?? 'Dent'),
                    'prestation_id' => $t->prestation_id,
                    'prestation_name' => $t->prestation?->name ?? null,
                    'phase' => $t->phase,
                ])->values()->toArray();
                @endphp
                <script>window.MDSMILE_TEETH_DATA = @json($teethData);</script>
                <div class="mt-4" id="odontogram-show-root"></div>
                @endif
            </div>

            {{-- Prestations par phase & Material (legacy) & Dates --}}
            <div class="grid gap-6 border-b border-[#967A4B]/20 pb-6 sm:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-[#967A4B] no-print">Prestations par phase</h3>
                    @if(count($phaseGroups) > 0)
                    <p class="mb-2 text-xs text-zinc-500 no-print">Cliquez sur une phase pour afficher uniquement ses dents dans le schéma.</p>
                    <div class="space-y-4">
                        @foreach($phaseGroups as $phaseNum => $prestList)
                        <button type="button" data-phase="{{ $phaseNum }}" class="odontogram-phase-trigger w-full rounded-lg border border-zinc-700/50 bg-zinc-800/30 p-3 text-left transition hover:border-[#967A4B]/50 hover:bg-zinc-800/50 focus:outline-none focus:ring-2 focus:ring-[#967A4B]/50 no-print" title="Afficher les dents de la phase {{ $phaseNum }}">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#967A4B]">Phase {{ $phaseNum }}</p>
                            @if(count($prestList) > 0)
                            <ul class="space-y-1.5 text-sm text-zinc-200">
                                @foreach($prestList as $pg)
                                <li><span class="font-medium text-[#967A4B]">{{ $pg['name'] }}</span> — Dents {{ implode(', ', $pg['teeth']) }}</li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-zinc-500 text-sm">—</p>
                            @endif
                        </button>
                        @endforeach
                    </div>
                    <button type="button" id="odontogram-phase-show-all" class="mt-2 text-xs text-zinc-400 underline hover:text-[#967A4B] focus:outline-none no-print">Afficher toutes les dents</button>
                    <script>
                    (function() {
                        document.querySelectorAll('.odontogram-phase-trigger').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var phase = parseInt(this.getAttribute('data-phase'), 10);
                                window.dispatchEvent(new CustomEvent('mdmile:selectPhase', { detail: phase }));
                                document.querySelectorAll('.odontogram-phase-trigger').forEach(function(b) { b.classList.remove('ring-2', 'ring-[#967A4B]'); });
                                this.classList.add('ring-2', 'ring-[#967A4B]');
                            });
                        });
                        var showAll = document.getElementById('odontogram-phase-show-all');
                        if (showAll) {
                            showAll.addEventListener('click', function() {
                                window.dispatchEvent(new CustomEvent('mdmile:selectPhase', { detail: null }));
                                document.querySelectorAll('.odontogram-phase-trigger').forEach(function(b) { b.classList.remove('ring-2', 'ring-[#967A4B]'); });
                            });
                        }
                    })();
                    </script>
                    @else
                    <p class="text-zinc-400">—</p>
                    @endif
                    @if(count($materialGroups) > 0)
                    <h3 class="mt-3 text-sm font-semibold text-[#967A4B]">Matériau (legacy)</h3>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach($materialGroups as $mg)
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium" style="background-color: {{ $mg['color'] }}30; color: {{ $mg['color'] }}">{{ $mg['name'] }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-[#967A4B]">Dates Clés</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="min-w-[5.5rem] text-zinc-400">Réception</span>
                            <span class="text-zinc-200">{{ $travail->date_entree->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="min-w-[5.5rem] text-zinc-400">Prévue</span>
                            <span class="text-zinc-200">{{ $travail->date_essiage ? $travail->date_essiage->format('d/m/Y') : '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="min-w-[5.5rem] text-zinc-400">Livraison</span>
                            <span class="text-zinc-200">{{ $travail->date_livraison->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Price (hidden for assistante) --}}
            @hasanyrole('manager|secretaire')
            <div>
                <h3 class="mb-2 text-sm font-semibold text-[#967A4B]">Prix</h3>
                <p class="text-xl font-bold text-[#967A4B]">Total Estimé: {{ number_format($travail->prix_actuel, 0, ',', ' ') }} DHS</p>
            </div>
            @endhasanyrole
        </div>
    </div>

    {{-- Action buttons (hidden when printing) --}}
    <div id="fiche-action-buttons" class="flex flex-wrap items-center gap-3">
        <a href="{{ route('travaux.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
        @hasanyrole('manager|secretaire|assistante|cadcam')
        <a href="{{ route('travaux.add-phase', $travail) }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-600/60 bg-emerald-600/20 px-4 py-2.5 text-sm font-medium text-emerald-300 transition hover:bg-emerald-600/30 no-print" title="Même(s) dent(s) avec une autre prestation (ex. essayage puis finition)">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Ajouter une phase
        </a>
        <a href="{{ route('travaux.edit', $travail) }}" class="inline-flex items-center gap-2 rounded-lg border border-[#967A4B]/50 bg-transparent px-4 py-2.5 text-sm font-medium text-[#967A4B] transition hover:bg-[#967A4B]/10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Modifier
        </a>
        @endhasanyrole
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-[#967A4B]/50 bg-transparent px-4 py-2.5 text-sm font-medium text-[#967A4B] transition hover:bg-[#967A4B]/10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Imprimer
        </button>
        @hasanyrole('manager|secretaire')
        <form method="POST" action="{{ route('travaux.destroy', $travail) }}" class="inline" onsubmit="return confirm('Supprimer ce travail ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-red-500/50 bg-transparent px-4 py-2.5 text-sm font-medium text-red-400 transition hover:bg-red-500/10">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Supprimer
            </button>
        </form>
        @endhasanyrole
    </div>
</div>
@endsection
