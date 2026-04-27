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

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
            <h1 class="text-xl font-bold text-[#967A4B]">Utilisateurs</h1>
            <p class="mt-1 text-sm text-zinc-400">Managers, secrétaires, assistantes, CAD/CAM</p>
        </div>
        <a href="{{ route('users.create') }}" class="shrink-0 rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B]">
            Ajouter un utilisateur
        </a>
    </div>

    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-4">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-4">
            <input type="text" name="recherche" value="{{ request('recherche') }}" placeholder="Nom ou email..." class="auth-input min-w-[200px] rounded-lg border border-zinc-600 bg-zinc-800/90 py-2.5 px-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
            <select name="role" class="auth-input rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30">
                <option value="">Tous les rôles</option>
                @foreach($roleLabels as $role => $label)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">Filtrer</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#967A4B]/20 bg-zinc-900/80">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead>
                    <tr class="border-b border-[#967A4B]/30 bg-zinc-800/80">
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Nom</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Email</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B]">Rôle</th>
                        <th class="px-4 py-3 font-semibold text-[#967A4B] w-28">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr class="border-b border-zinc-800 hover:bg-zinc-800/50">
                        <td class="px-4 py-3 font-medium text-zinc-200">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            @php $roleName = $u->roles->first()?->name; @endphp
                            <span class="rounded-full border border-[#967A4B]/40 bg-[#967A4B]/10 px-2.5 py-0.5 text-xs font-medium text-[#967A4B]">{{ $roleLabels[$roleName] ?? $roleName ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('users.edit', $u) }}" class="rounded p-1.5 text-[#967A4B] hover:bg-[#967A4B]/10" title="Modifier">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded p-1.5 text-red-400 hover:bg-red-500/10" title="Supprimer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-zinc-500">Aucun utilisateur.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="flex items-center justify-center gap-2 border-t border-zinc-800 px-4 py-3">
            <span class="text-sm text-zinc-400">Page {{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
            <div class="flex gap-1">
                @if($users->onFirstPage())
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
                @endif
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B] hover:bg-[#967A4B]/20"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-500 cursor-not-allowed"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
