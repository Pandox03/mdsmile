@extends('layouts.dashboard')

@section('content')
@php
    $month->locale('fr');
    $selected->locale('fr');
    $prevMonth = $month->copy()->subMonth();
    $nextMonth = $month->copy()->addMonth();
@endphp
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-col gap-1">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-zinc-100">
            <svg class="h-7 w-7 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Calendrier
        </h1>
        <p class="text-sm text-zinc-500">Réceptions, livraisons et essiages par jour</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,380px)_1fr]">
        {{-- Calendar: fixed 7-column grid so it never stacks vertically --}}
        <div class="min-w-0 rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4 sm:p-5" style="min-width: 280px;">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold capitalize text-zinc-100">{{ $month->translatedFormat('F Y') }}</h2>
                <div class="flex items-center gap-1">
                    <a href="{{ route('calendrier.index', ['month' => $prevMonth->format('Y-m'), 'date' => $selected->format('Y-m-d')]) }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-800 text-zinc-400 transition hover:bg-[#967A4B]/20 hover:text-[#967A4B]" aria-label="Mois précédent">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <a href="{{ route('calendrier.index', ['month' => $nextMonth->format('Y-m'), 'date' => $selected->format('Y-m-d')]) }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-800 text-zinc-400 transition hover:bg-[#967A4B]/20 hover:text-[#967A4B]" aria-label="Mois suivant">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div class="grid text-center text-xs font-medium uppercase text-zinc-500" style="grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 2px;">
                @foreach($jourNoms as $j)
                <div class="py-1">{{ $j }}</div>
                @endforeach
            </div>
            <div class="mt-1 grid gap-0.5" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                @foreach($weeks as $week)
                    @foreach($week as $cell)
                    @php
                        $isSelected = $cell['dateStr'] === $selected->format('Y-m-d');
                        $hasTravaux = isset($daysWithTravaux[$cell['dateStr']]);
                    @endphp
                    <a href="{{ route('calendrier.index', ['month' => $month->format('Y-m'), 'date' => $cell['dateStr']]) }}"
                       class="flex aspect-square min-w-0 items-center justify-center rounded-lg text-sm transition {{ $cell['isCurrentMonth'] ? 'text-zinc-200' : 'text-zinc-600' }} {{ $isSelected ? 'bg-[#967A4B] text-black font-semibold' : ($hasTravaux ? 'bg-[#967A4B]/25 hover:bg-[#967A4B]/35' : 'hover:bg-zinc-800') }}">
                        {{ $cell['day'] }}
                    </a>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Selected day & travaux --}}
        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4 sm:p-5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h2 class="flex items-center gap-2 text-lg font-semibold text-zinc-100">
                    <svg class="h-5 w-5 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="capitalize">{{ $selected->translatedFormat('l d F') }}</span>
                </h2>
                @hasanyrole('manager|secretaire|assistante')
                <a href="{{ route('travaux.create') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#967A4B] text-black transition hover:bg-[#B8986B]" title="Nouveau travail">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </a>
                @endhasanyrole
            </div>
            <ul class="space-y-3">
                @forelse($travauxDuJour as $item)
                @php $t = $item['travail']; @endphp
                <li>
                    <a href="{{ route('travaux.show', $t) }}" class="flex items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-800/50 p-3 transition hover:border-[#967A4B]/40 hover:bg-zinc-800">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#967A4B]/20">
                            <svg class="h-5 w-5 text-[#967A4B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-200">{{ $t->type_travail_display }}</p>
                            <p class="truncate text-xs text-zinc-400">{{ $t->reference }} · {{ $t->doc->name ?? $t->dentiste ?? '—' }}</p>
                            @if(count($item['types']) > 0)
                            <p class="mt-0.5 text-xs text-[#967A4B]">{{ implode(' · ', $item['types']) }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 rounded-full border border-zinc-600 bg-zinc-800 px-2.5 py-0.5 text-xs text-zinc-400">{{ $statutLabels[$t->statut] ?? $t->statut }}</span>
                        <svg class="h-4 w-4 shrink-0 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </li>
                @empty
                <li class="rounded-lg border border-zinc-800 bg-zinc-800/50 p-6 text-center text-sm text-zinc-500">Aucun travail (réception, livraison ou essiage) pour ce jour.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
