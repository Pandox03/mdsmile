@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
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

    {{-- Title + Add button (creation client réservée au manager) --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Clients (Dentistes)</h1>
        </div>
        @role('manager')
        <a href="{{ route('clients.create') }}" class="shrink-0 rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
            Ajouter un client
        </a>
        @endrole
    </div>

    {{-- Search --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap items-center gap-4">
            <div class="relative min-w-[200px] flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="recherche" value="{{ request('recherche') }}" placeholder="Rechercher par nom, n° enregistrement, téléphone, email..." class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 pl-10 pr-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            </div>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
                Rechercher
            </button>
            @if(request('recherche'))
            <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#967A4B]/50 bg-transparent px-4 py-2.5 text-sm font-medium text-[#967A4B] transition hover:bg-[#967A4B]/10">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Réinitialiser
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-[#967A4B]/20 bg-zinc-900/80">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Nom</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">N° enregistrement</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Téléphone</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Email</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Adresse</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Travaux</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] w-28">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                    <tr role="button" tabindex="0" onclick="window.location='{{ route('clients.show', $doc) }}'" onkeydown="if(event.key==='Enter') window.location='{{ route('clients.show', $doc) }}'" class="cursor-pointer border-b border-zinc-800 transition hover:bg-zinc-800/50">
                        <td class="px-4 py-3 font-medium text-zinc-200">{{ $doc->name }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $doc->numero_registration ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $doc->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $doc->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-400 max-w-[200px] truncate" title="{{ $doc->adresse }}">{{ $doc->adresse ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $doc->travaux_count }}</td>
                        <td class="px-4 py-3" onclick="event.stopPropagation()">
                            <div class="flex items-center gap-2">
                                @role('manager')
                                <a href="{{ route('clients.prestations', $doc) }}" class="rounded p-1.5 font-bold text-[#967A4B] transition hover:bg-[#967A4B]/10" title="Tarifs client">$</a>
                                <a href="{{ route('clients.edit', $doc) }}" class="rounded p-1.5 text-[#967A4B] transition hover:bg-[#967A4B]/10" title="Modifier">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('clients.destroy', $doc) }}" class="inline" onsubmit="return confirm('Supprimer ce client ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded p-1.5 text-red-400 transition hover:bg-red-500/10" title="Supprimer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-zinc-500">Aucun client (dentiste) trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($docs->hasPages())
        <div class="flex items-center justify-center gap-2 border-t border-zinc-800 px-4 py-3">
            <span class="text-sm text-zinc-400">Page {{ $docs->currentPage() }} sur {{ $docs->lastPage() }}</span>
            <div class="flex gap-1">
                @if($docs->onFirstPage())
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $docs->previousPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif
                @if($docs->hasMorePages())
                    <a href="{{ $docs->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
