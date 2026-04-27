@php
    $welcome = 'Bonjour ! Je suis l\'assistant MdSmile. Posez-moi une question sur l\'utilisation de l\'application (ex: "Comment créer un travail ?", "Où sont les factures ?"). Les réponses sont adaptées à votre rôle.';
@endphp
<div class="fixed bottom-20 right-4 z-40 sm:bottom-6 sm:right-6 no-print" x-data="chatbotWidget()" x-cloak>
    {{-- Toggle button --}}
    <button type="button"
            @click="open = !open"
            class="flex h-14 w-14 items-center justify-center rounded-full border-2 border-[#967A4B]/60 bg-zinc-900 shadow-lg text-[#967A4B] hover:bg-[#967A4B]/20 hover:border-[#967A4B] transition"
            :class="open ? 'bg-[#967A4B]/20 border-[#967A4B]' : ''"
            title="Aide et guide d'utilisation">
        <svg x-show="!open" class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <svg x-show="open" class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    {{-- Chat panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="absolute bottom-full right-0 mb-2 flex h-[420px] w-[340px] flex-col overflow-hidden rounded-xl border border-[#967A4B]/30 bg-zinc-900 shadow-xl sm:w-[380px]">
        <div class="border-b border-[#967A4B]/30 bg-zinc-800/80 px-4 py-3">
            <h3 class="text-sm font-semibold text-[#967A4B]">Assistant MdSmile</h3>
            <p class="text-xs text-zinc-400">Guide selon votre rôle</p>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chatbot-messages" x-ref="messages">
            <div class="flex justify-start">
                <div class="max-w-[90%] rounded-lg rounded-tl-none border border-zinc-700 bg-zinc-800/80 px-3 py-2 text-sm text-zinc-200">
                    {{ $welcome }}
                </div>
            </div>
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'rounded-lg rounded-tr-none border border-[#967A4B]/40 bg-[#967A4B]/20 px-3 py-2 text-sm text-zinc-100' : 'max-w-[90%] rounded-lg rounded-tl-none border border-zinc-700 bg-zinc-800/80 px-3 py-2 text-sm text-zinc-200'"
                         x-html="msg.role === 'user' ? escapeHtml(msg.content) : msg.content"></div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="rounded-lg rounded-tl-none border border-zinc-700 bg-zinc-800/80 px-3 py-2 text-sm text-zinc-400">
                    Réflexion…
                </div>
            </div>
        </div>
        <form @submit.prevent="send()" class="border-t border-zinc-800 p-3">
            <div class="flex gap-2">
                <input type="text"
                       x-model="input"
                       placeholder="Posez votre question…"
                       class="auth-input flex-1 rounded-lg border border-zinc-600 bg-zinc-800/90 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30"
                       maxlength="500"
                       autocomplete="off">
                <button type="submit"
                        :disabled="loading || !input.trim()"
                        class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black transition hover:bg-[#B8986B] disabled:opacity-50 disabled:cursor-not-allowed">
                    Envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('chatbotWidget', function() {
        return {
            open: false,
            input: '',
            messages: [],
            loading: false,
            send() {
                var text = (this.input || '').trim();
                if (!text || this.loading) return;
                this.messages.push({ role: 'user', content: text });
                this.input = '';
                this.loading = true;
                var self = this;
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('{{ route("chatbot.ask") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message: text })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    self.loading = false;
                    self.messages.push({ role: 'assistant', content: data.response || 'Désolé, je n\'ai pas de réponse pour cette question.' });
                    self.$nextTick(function() {
                        var el = self.$refs.messages;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                })
                .catch(function() {
                    self.loading = false;
                    self.messages.push({ role: 'assistant', content: 'Une erreur est survenue. Réessayez ou posez une autre question.' });
                });
            },
            escapeHtml(s) {
                var div = document.createElement('div');
                div.textContent = s;
                return div.innerHTML;
            }
        };
    });
});
</script>
