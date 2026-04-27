@extends('layouts.dashboard')
@php use Illuminate\Support\Facades\Storage; @endphp

@section('content')
<div class="mx-auto flex max-h-[calc(100vh-7rem)] max-w-2xl flex-col rounded-xl border border-[#967A4B]/25 bg-zinc-900/90 shadow-xl">
    {{-- Header --}}
    <div class="flex shrink-0 items-center justify-between border-b border-[#967A4B]/20 bg-zinc-800/60 px-5 py-3.5">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#967A4B]/20 text-[#967A4B]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <h1 class="text-base font-semibold text-zinc-100">Chat équipe</h1>
                <p class="text-xs text-zinc-500">Messages et images pour toute l'équipe</p>
            </div>
        </div>
    </div>

    {{-- Success / error --}}
    @if(session('success'))
    <div class="mx-4 mt-3 rounded-lg bg-emerald-500/15 px-4 py-2 text-sm text-emerald-300 ring-1 ring-emerald-500/30">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mx-4 mt-3 rounded-lg bg-red-500/15 px-4 py-2 text-sm text-red-300 ring-1 ring-red-500/30">
        {{ $errors->first() }}
    </div>
    @endif

    {{-- Messages --}}
    <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
        <div class="flex-1 overflow-y-auto px-4 py-4" id="chat-messages" x-data="teamChat()" x-init="scrollToBottom()">
            <div class="space-y-5">
                @forelse($messages as $msg)
                <div class="flex gap-3 {{ $msg->user_id === auth()->id() ? 'flex-row-reverse justify-end' : 'justify-start' }}">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $msg->user_id === auth()->id() ? 'bg-[#967A4B]/25 text-[#967A4B]' : 'bg-zinc-600 text-zinc-200' }}">
                        {{ strtoupper(substr($msg->user->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex min-w-0 max-w-[78%] flex-col {{ $msg->user_id === auth()->id() ? 'items-end' : 'items-start' }}">
                        <span class="mb-1 block text-xs text-zinc-500">
                            {{ $msg->user->name ?? 'Utilisateur' }}
                            <span class="ml-1.5">{{ $msg->created_at->format('d/m H:i') }}</span>
                        </span>
                        <div class="w-full rounded-2xl px-4 py-2.5 {{ $msg->user_id === auth()->id() ? 'rounded-br-md bg-[#967A4B]/25 text-zinc-100' : 'rounded-bl-md bg-zinc-800 text-zinc-200' }}">
                            @if($msg->body)
                            <p class="whitespace-pre-wrap break-words text-sm leading-relaxed">{{ $msg->body }}</p>
                            @endif
                            @if($msg->attachment_path)
                            <div class="{{ $msg->body ? 'mt-3' : '' }}">
                                <a href="{{ Storage::url($msg->attachment_path) }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-lg ring-1 ring-zinc-600/50 transition hover:ring-[#967A4B]/50">
                                    <img src="{{ Storage::url($msg->attachment_path) }}" alt="{{ $msg->attachment_name ?? 'Image' }}" class="max-h-52 w-full object-contain">
                                </a>
                                @if($msg->attachment_name)
                                <p class="mt-1 truncate text-xs text-zinc-500">{{ $msg->attachment_name }}</p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-800 text-zinc-500">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-zinc-400">Aucun message</p>
                    <p class="mt-1 text-xs text-zinc-500">Envoyez le premier message ci‑dessous</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Composer row - attach, message, send --}}
        <form action="{{ route('chat.store') }}" method="POST" enctype="multipart/form-data" class="shrink-0 border-t border-zinc-700/80 bg-zinc-800/50 p-4">
            @csrf
            <div class="flex items-center gap-3">
                <label class="flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-zinc-600 bg-zinc-700/80 text-zinc-400 transition hover:border-[#967A4B]/50 hover:bg-zinc-700 hover:text-[#967A4B]" title="Joindre une image">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="file" name="attachment" accept="image/*" class="hidden">
                </label>
                <div class="min-w-0 flex-1">
                    <textarea name="body" rows="1" maxlength="2000" placeholder="Écrire un message…"
                        class="w-full resize-none rounded-xl border border-zinc-600 bg-zinc-800 px-4 py-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B]/60 focus:outline-none focus:ring-2 focus:ring-[#967A4B]/20"
                        style="min-height: 44px;"
                    >{{ old('body') }}</textarea>
                    <p id="file-name" class="mt-1 truncate pl-1 text-xs text-zinc-500" style="display: none;"></p>
                </div>
                <button type="submit" class="flex h-11 shrink-0 items-center justify-center gap-1.5 rounded-xl bg-[#967A4B] px-4 text-sm font-medium text-white transition hover:bg-[#a88655] focus:outline-none focus:ring-2 focus:ring-[#967A4B]/50" title="Envoyer">
                    <span class="hidden sm:inline">Envoyer</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('teamChat', function() {
        return {
            scrollToBottom() {
                this.$nextTick(function() {
                    var el = document.getElementById('chat-messages');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            }
        };
    });
});
document.querySelector('input[name="attachment"]')?.addEventListener('change', function(e) {
    var name = e.target.files?.length ? e.target.files[0].name : '';
    var el = document.getElementById('file-name');
    if (el) {
        el.textContent = name || '';
        el.style.display = name ? 'block' : 'none';
    }
});
</script>
@endsection
