@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Journaux d'activité</h1>
        <p class="mt-1 text-sm text-zinc-400">Historique des créations, modifications et suppressions par les utilisateurs</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('logs.index') }}" class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-500">Type</label>
                <select name="subject_type" class="rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    <option value="">Tous les types</option>
                    @foreach($subjectTypeLabels as $value => $label)
                    <option value="{{ $value }}" {{ request('subject_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-500">Action</label>
                <select name="action" class="rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                    <option value="">Toutes</option>
                    @foreach($actionLabels as $value => $label)
                    <option value="{{ $value }}" {{ request('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2 text-sm font-medium text-black hover:bg-[#B8986B]">Filtrer</button>
            @if(request()->hasAny(['subject_type', 'action']))
            <a href="{{ route('logs.index') }}" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800">Réinitialiser</a>
            @endif
        </div>
    </form>

    {{-- Logs table --}}
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-700 bg-zinc-800/50">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Date / Heure</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Utilisateur</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Type</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Action</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="border-b border-zinc-800 hover:bg-zinc-800/50">
                        <td class="px-4 py-3 text-zinc-300 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-zinc-300">
                            {{ $log->user?->name ?? '—' }}
                            @if($log->user)
                            <span class="text-zinc-500 text-xs block">{{ $log->user->email }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full border border-[#967A4B]/40 bg-[#967A4B]/10 px-2.5 py-0.5 text-xs text-[#967A4B]">{{ $log->subject_type_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($log->action === 'created')
                            <span class="text-emerald-400">Création</span>
                            @elseif($log->action === 'updated')
                            <span class="text-amber-400">Modification</span>
                            @else
                            <span class="text-red-400">Suppression</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-300">{{ $log->description ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-zinc-500">Aucune activité enregistrée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="border-t border-zinc-700 px-4 py-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
