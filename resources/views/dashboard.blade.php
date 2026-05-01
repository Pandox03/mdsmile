@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    {{-- Page title --}}
    <div class="flex flex-col gap-1">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-zinc-100">
            <svg class="h-7 w-7 text-[#967A4B]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10l10 5 10-5V7L12 2zm0 2.18l6.9 3.45V15.1L12 18.55l-6.9-3.45V7.63L12 4.18z"/></svg>
            Tableau de bord
        </h1>
        <p class="text-sm text-zinc-500">{{ config('app.name') }} — Gestion laboratoire dentaire</p>
    </div>

    {{-- KPI cards (visibility by role: manager+secretaire = all, assistante = travaux+caisse, cadcam = travaux only) --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @hasanyrole('manager|secretaire|assistante|cadcam')
        <a href="{{ route('travaux.index') }}" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-5 transition hover:border-[#967A4B]/40 hover:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-400">Travaux en cours</p>
                    <p class="mt-1 text-2xl font-bold text-zinc-100">{{ number_format($travauxEnCours, 0, ',', ' ') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">En attente + en cours</p>
                </div>
                <div class="rounded-lg bg-[#967A4B]/20 p-2.5">
                    <svg class="h-6 w-6 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
        </a>
        @role('assistante')
        <a href="{{ route('caisse.index') }}" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-5 transition hover:border-[#967A4B]/40 hover:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-400">Caisse</p>
                    <p class="mt-1 text-sm font-medium text-zinc-300">Mouvements et encaissements</p>
                    <p class="mt-1 text-xs text-zinc-500">Voir la caisse</p>
                </div>
                <div class="rounded-lg bg-[#967A4B]/20 p-2.5">
                    <svg class="h-6 w-6 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2h-2m-4-1V9a2 2 0 012-2h2a2 2 0 012 2v1m-4 4h10"/></svg>
                </div>
            </div>
        </a>
        @endrole
        @endhasanyrole

        @hasanyrole('manager|secretaire')
        @role('manager')
        <a href="{{ route('factures.index') }}" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-5 transition hover:border-[#967A4B]/40 hover:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-400">Chiffre d'affaires (DHS)</p>
                    <p class="mt-1 text-2xl font-bold text-zinc-100">{{ number_format($chiffreAffaires, 0, ',', ' ') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">Total factures</p>
                </div>
                <div class="rounded-lg bg-[#967A4B]/20 p-2.5">
                    <svg class="h-6 w-6 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>
        @endrole

        <a href="{{ route('factures.index') }}" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-5 transition hover:border-[#967A4B]/40 hover:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-400">Statut paiement</p>
                    <p class="mt-1 text-2xl font-bold text-zinc-100">Désactivé</p>
                    <p class="mt-1 text-xs text-zinc-500">Les factures ne gèrent plus ce statut</p>
                </div>
                <div class="rounded-lg bg-[#967A4B]/20 p-2.5">
                    <svg class="h-6 w-6 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </a>

        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-5 transition hover:border-[#967A4B]/40" x-data="{ open: false }">
            <div class="flex items-start justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-zinc-400">Stock faible</p>
                    @if($stockFaible > 0)
                        <button type="button" @click="open = !open" class="mt-1 flex items-center gap-1.5 text-left">
                            <span class="text-2xl font-bold text-zinc-100">{{ $stockFaible }} article(s)</span>
                            <svg class="h-5 w-5 text-zinc-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    @else
                        <p class="mt-1 text-2xl font-bold text-zinc-100">0 article(s)</p>
                    @endif
                    <p class="mt-1 text-xs {{ $stockFaible > 0 ? 'text-amber-400' : 'text-zinc-500' }}">Quantité &lt; 5</p>
                </div>
                <a href="{{ route('stock.index') }}" class="rounded-lg bg-[#967A4B]/20 p-2.5 hover:bg-[#967A4B]/30">
                    <svg class="h-6 w-6 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </a>
            </div>
            @if($stockFaible > 0)
            <div x-show="open" x-transition class="mt-3 border-t border-zinc-700/80 pt-3">
                <p class="mb-2 text-xs font-medium text-zinc-500">Articles concernés :</p>
                <ul class="space-y-1.5">
                    @foreach($stockFaibleItems as $item)
                    <li class="flex items-center justify-between rounded-lg bg-zinc-800/60 px-2.5 py-2 text-sm">
                        <span class="truncate font-medium text-zinc-200">{{ $item->name }}</span>
                        <span class="ml-2 shrink-0 text-zinc-400">{{ number_format($item->quantity, 0, ',', ' ') }} {{ $item->unite }}</span>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('stock.index') }}" class="mt-2 inline-block text-xs font-medium text-[#967A4B] hover:underline">Voir tout le stock →</a>
            </div>
            @endif
        </div>
        @endhasanyrole
    </div>

    {{-- Livraisons demain (manager, secretaire, assistante, cadcam) --}}
    @hasanyrole('manager|secretaire|assistante|cadcam')
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-zinc-100">Livraisons demain</h2>
            <a href="{{ route('travaux.index') }}" class="rounded p-1.5 text-[#967A4B] hover:bg-[#967A4B]/10" title="Voir les travaux">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <ul class="space-y-3">
            @forelse($livraisonsDemain as $t)
            <li class="flex items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-800/50 p-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#967A4B]/20">
                    <svg class="h-4 w-4 text-[#967A4B]" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-zinc-200">{{ $t->doc->name ?? $t->dentiste ?? '—' }}</p>
                    <p class="truncate text-xs text-zinc-400">{{ $t->type_travail_display }} — {{ $t->reference }}</p>
                </div>
                <span class="shrink-0 text-sm font-medium text-zinc-300">{{ $t->date_livraison->format('d/m/Y') }}</span>
                <a href="{{ route('travaux.show', $t) }}" class="shrink-0 rounded-lg border border-[#967A4B]/50 px-3 py-1.5 text-xs font-medium text-[#967A4B] hover:bg-[#967A4B]/10">Voir</a>
            </li>
            @empty
            <li class="rounded-lg border border-zinc-800 bg-zinc-800/50 p-4 text-center text-sm text-zinc-500">Aucune livraison prévue demain.</li>
            @endforelse
        </ul>
    </div>
    @endhasanyrole

    {{-- Chart + Derniers Travaux (chart only for manager+secretaire) --}}
    <div class="grid gap-6 lg:grid-cols-3">
        @role('manager')
        {{-- Évolution du Chiffre d'Affaires --}}
        <div class="lg:col-span-2 rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-100">Évolution du chiffre d'affaires (DHS) — 6 derniers mois</h2>
            <div class="h-64">
                <canvas id="revenueChart" class="w-full" height="256"></canvas>
            </div>
        </div>
        @endrole

        {{-- Derniers Travaux (manager, secretaire, assistante, cadcam) --}}
        @hasanyrole('manager|secretaire|assistante|cadcam')
        <div class="{{ auth()->user()->hasRole('manager') ? '' : 'lg:col-span-3' }} rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-zinc-100">Derniers travaux</h2>
                <a href="{{ route('travaux.index') }}" class="rounded p-1.5 text-[#967A4B] hover:bg-[#967A4B]/10" title="Voir tout">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <ul class="space-y-3">
                @forelse($derniersTravaux as $t)
                <li class="flex items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-800/50 p-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#967A4B]/20">
                        <svg class="h-4 w-4 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-200">{{ $t->patient ?: '—' }}</p>
                        <p class="truncate text-xs text-zinc-500">{{ $t->type_travail ?: '—' }} · {{ $t->doc->name ?? '—' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full border border-zinc-600 bg-zinc-800 px-2.5 py-0.5 text-xs text-zinc-400">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                    <a href="{{ route('travaux.show', $t) }}" class="shrink-0 rounded-lg border border-[#967A4B]/50 px-3 py-1.5 text-xs font-medium text-[#967A4B] hover:bg-[#967A4B]/10">Voir</a>
                </li>
                @empty
                <li class="rounded-lg border border-zinc-800 bg-zinc-800/50 p-4 text-center text-sm text-zinc-500">Aucun travail.</li>
                @endforelse
            </ul>
        </div>
        @endhasanyrole
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    var labels = @json($chartLabels);
    var data = @json($chartData);
    var maxVal = Math.max.apply(null, data);
    if (maxVal === 0) maxVal = 1;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Chiffre d\'affaires (DHS)',
                data: data,
                borderColor: '#967A4B',
                backgroundColor: 'rgba(150, 122, 75, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: Math.ceil(maxVal * 1.2 / 1000) * 1000 || 1000,
                    ticks: {
                        color: '#a1a1aa',
                        callback: function(v) { return (v / 1000) + ' k'; }
                    },
                    grid: { color: 'rgba(255,255,255,0.06)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#a1a1aa' }
                }
            }
        }
    });
});
</script>
@endsection
