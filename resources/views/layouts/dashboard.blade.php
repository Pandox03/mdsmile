<x-layouts.app :title="$title ?? 'MdSmile'">
    <div class="min-h-screen flex flex-col">
        {{-- Header --}}
        <header class="sticky top-0 z-50 border-b border-[#967A4B]/30 bg-zinc-900/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="/" class="flex items-center gap-3">
                    <img src="{{ asset('images/mdsmile-logo.png') }}" alt="MdSmile" class="h-10 w-auto object-contain" />
                    <span class="hidden text-sm text-zinc-500 sm:inline">— Gestion Laboratoire Dentaire</span>
                </a>
                <div class="flex items-center gap-4">
                    @php
                        $navSuggestions = [['label' => 'Tableau de bord', 'url' => route('dashboard')]];
                        if (auth()->user()->hasAnyRole(['manager', 'secretaire', 'assistante', 'cadcam'])) {
                            $navSuggestions[] = ['label' => 'Chat équipe', 'url' => route('chat.index')];
                        }
                        if (auth()->user()->hasAnyRole(['manager', 'secretaire'])) {
                            $navSuggestions = array_merge($navSuggestions, [
                                ['label' => 'Travaux', 'url' => route('travaux.index')],
                                ['label' => 'Calendrier', 'url' => route('calendrier.index')],
                                ['label' => 'Clients', 'url' => route('clients.index')],
                                ['label' => 'Factures', 'url' => route('factures.index')],
                                ['label' => 'Situations doc', 'url' => route('doc.situations.index')],
                                ['label' => 'Stock', 'url' => route('stock.index')],
                                ['label' => 'Caisse', 'url' => route('caisse.index')],
                                ['label' => 'Prestations', 'url' => route('prestations.index')],
                            ]);
                        } elseif (auth()->user()->hasRole('assistante')) {
                            $navSuggestions = array_merge($navSuggestions, [
                                ['label' => 'Travaux', 'url' => route('travaux.index')],
                                ['label' => 'Calendrier', 'url' => route('calendrier.index')],
                                ['label' => 'Caisse', 'url' => route('caisse.index')],
                            ]);
                        } elseif (auth()->user()->hasRole('cadcam')) {
                            $navSuggestions = array_merge($navSuggestions, [
                                ['label' => 'Travaux', 'url' => route('travaux.index')],
                                ['label' => 'Calendrier', 'url' => route('calendrier.index')],
                                ['label' => 'Stock', 'url' => route('stock.index')],
                            ]);
                        }
                        if (auth()->user()->hasRole('manager')) {
                            $navSuggestions = array_merge($navSuggestions, [
                                ['label' => 'Paramètres', 'url' => route('parametres.index')],
                                ['label' => 'Prestations', 'url' => route('prestations.index')],
                                ['label' => 'Journaux', 'url' => route('logs.index')],
                                ['label' => 'Utilisateurs', 'url' => route('users.index')],
                            ]);
                        }
                    @endphp
                    <script type="application/json" id="header-nav-suggestions">{!! json_encode($navSuggestions) !!}</script>
                    <script>
                    (function() {
                        function register() {
                            Alpine.data('headerSearch', function() {
                                var items = [];
                                try {
                                    var el = document.getElementById('header-nav-suggestions');
                                    if (el && el.textContent) items = JSON.parse(el.textContent);
                                } catch (e) {}
                                return {
                                    query: '',
                                    open: false,
                                    items: items,
                                    get filtered() {
                                        var q = (this.query || '').trim().toLowerCase();
                                        if (!q) return this.items;
                                        return this.items.filter(function(i) { return i.label.toLowerCase().includes(q); });
                                    }
                                };
                            });
                        }
                        if (window.Alpine) register(); else document.addEventListener('alpine:init', register);
                    })();
                    </script>
                    <div class="relative w-48 sm:w-64" x-data="headerSearch()" @click.outside="open = false">
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-zinc-500 pointer-events-none">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text"
                                   x-model="query"
                                   @focus="open = true"
                                   @keydown.escape="open = false"
                                   placeholder="Aller à une section… (ex: tr → Travaux)"
                                   class="w-full rounded-lg border border-[#967A4B]/50 bg-zinc-800/50 py-2 pl-9 pr-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-[#967A4B]/50 focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30"
                                   aria-label="Aller à une section"
                                   autocomplete="off">
                        </div>
                        <div x-show="open && query.length > 0 && filtered.length > 0"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-cloak
                             class="absolute left-0 right-0 top-[calc(100%+0.25rem)] z-50 max-h-72 overflow-auto rounded-lg border border-zinc-700 bg-zinc-900 shadow-xl">
                            <template x-for="item in filtered" :key="item.url">
                                <a :href="item.url"
                                   @click="open = false"
                                   class="block border-b border-zinc-800 px-4 py-2.5 text-sm text-zinc-200 hover:bg-[#967A4B]/20 hover:text-[#967A4B] first:rounded-t-lg last:border-0"
                                   x-text="item.label"></a>
                            </template>
                        </div>
                    </div>
                    @hasanyrole('manager|secretaire|assistante|cadcam')
                    @php
                        $notificationCount = $notificationCount ?? 0;
                        $notificationTravaux = $notificationTravaux ?? collect();
                    @endphp
                    <div class="relative overflow-visible" x-data="{ notificationsOpen: false }" @click.outside="notificationsOpen = false">
                        <button type="button"
                                @click="notificationsOpen = !notificationsOpen"
                                class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-visible rounded-lg border border-[#967A4B]/30 bg-zinc-800/50 text-zinc-400 transition hover:border-[#967A4B]/50 hover:bg-zinc-800 hover:text-[#967A4B]"
                                title="Travaux à livrer ou à essayer dans les 24 h"
                                aria-label="Notifications ({{ $notificationCount }})">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @if($notificationCount > 0)
                            <span class="absolute right-0 top-0 z-10 inline-flex h-3 w-3 -translate-y-1/2 translate-x-1/2 items-center justify-center rounded-full bg-red-500 text-[8px] font-semibold leading-none text-white shadow ring-2 ring-zinc-900">{{ $notificationCount > 99 ? '99+' : $notificationCount }}</span>
                            @endif
                        </button>
                        <div x-show="notificationsOpen"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-cloak
                             class="absolute right-0 top-[calc(100%+0.5rem)] z-50 w-80 overflow-hidden rounded-lg border border-zinc-700 bg-zinc-900 shadow-xl">
                            <div class="border-b border-zinc-700 bg-zinc-800/80 px-4 py-3">
                                <p class="text-sm font-semibold text-zinc-100">À livrer ou essayer sous 24 h</p>
                                <p class="text-xs text-zinc-500">Livraison ou essiage aujourd'hui ou demain</p>
                            </div>
                            <div class="max-h-80 overflow-auto">
                                @forelse($notificationTravaux as $nt)
                                <a href="{{ $nt['url'] }}" @click="notificationsOpen = false" class="block border-b border-zinc-800 px-4 py-3 text-left transition hover:bg-[#967A4B]/10">
                                    <p class="truncate text-sm font-medium text-zinc-200">{{ $nt['patient'] ?: '—' }}</p>
                                    <p class="truncate text-xs text-zinc-400">{{ $nt['type_travail_display'] }} — {{ $nt['reference'] }}</p>
                                    <p class="mt-1 text-xs text-[#967A4B]">{{ implode(' · ', $nt['deadlines']) }}</p>
                                </a>
                                @empty
                                <p class="px-4 py-6 text-center text-sm text-zinc-500">Aucun travail à livrer ou essayer sous 24 h.</p>
                                @endforelse
                            </div>
                            @if($notificationCount > 0)
                            <div class="border-t border-zinc-700 bg-zinc-800/50 px-4 py-2">
                                <a href="{{ route('travaux.index') }}" @click="notificationsOpen = false" class="block text-center text-xs font-medium text-[#967A4B] hover:underline">Voir tous les travaux</a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endhasanyrole
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-lg border border-[#967A4B]/30 bg-zinc-800/50 px-3 py-2 transition hover:border-[#967A4B]/50 hover:bg-zinc-800" title="Mon profil">
                        <div class="h-8 w-8 rounded-full bg-[#967A4B]/20 flex items-center justify-center text-[#967A4B] text-sm font-medium">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-medium text-zinc-100">{{ auth()->user()->name ?? 'Utilisateur' }}</p>
                            <p class="text-xs text-zinc-500">{{ auth()->user()->getRoleNames()->first() ?? '—' }}</p>
                        </div>
                    </a>
                </div>
            </div>
        </header>

        <div class="flex flex-1">
            {{-- Sidebar --}}
            <aside class="hidden w-64 flex-shrink-0 border-r border-[#967A4B]/20 bg-zinc-900/50 lg:block">
                <nav class="space-y-1 p-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('dashboard') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Tableau de Bord</span>
                    </a>
                    @hasanyrole('manager|secretaire|assistante|cadcam')
                    <a href="{{ route('travaux.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('travaux.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Travaux</span>
                    </a>
                    <a href="{{ route('calendrier.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('calendrier.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Calendrier</span>
                    </a>
                    <a href="{{ route('chat.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('chat.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <span class="relative shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @if(($unreadChatCount ?? 0) > 0)
                            <span class="absolute -right-1.5 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-zinc-900">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
                            @endif
                        </span>
                        <span>Chat équipe</span>
                    </a>
                    @hasanyrole('manager|secretaire')
                    <a href="{{ route('clients.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('clients.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Clients</span>
                    </a>
                    <a href="{{ route('factures.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('factures.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Facturation</span>
                    </a>
                    <a href="{{ route('doc.situations.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('doc.situations.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm12-10a2 2 0 01-2 2h-2a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>
                        <span>Situations doc</span>
                    </a>
                    @endhasanyrole
                    @hasanyrole('manager|secretaire|cadcam')
                    <a href="{{ route('stock.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('stock.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Stock</span>
                    </a>
                    @endhasanyrole
                    @hasanyrole('manager|secretaire|assistante')
                    <a href="{{ route('caisse.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('caisse.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2h-2m-4-1V9a2 2 0 012-2h2a2 2 0 012 2v1m-4 4h10"/></svg>
                        <span>Caisse</span>
                    </a>
                    @endhasanyrole
                    @endhasanyrole
                    @hasanyrole('manager|secretaire')
                    <a href="{{ route('prestations.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('prestations.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Prestations</span>
                    </a>
                    @endhasanyrole
                    @role('manager')
                    <a href="{{ route('parametres.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('parametres.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Paramètres</span>
                    </a>
                    <a href="{{ route('logs.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('logs.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Journaux</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('users.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Utilisateurs</span>
                    </a>
                    @endrole
                    <hr class="my-4 border-[#967A4B]/20">
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-zinc-500 hover:bg-red-500/10 hover:text-red-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Déconnexion</span>
                        </button>
                    </form>
                </nav>
            </aside>

            {{-- Main content --}}
            <main class="flex-1 overflow-auto p-6">
                @yield('content')
            </main>
        </div>

        {{-- Mobile bottom nav (Tableau de Bord, Travaux, Clients, Facturation, Stock, Caisse, Paramètres, Déconnexion) --}}
        <nav class="flex justify-around border-t border-[#967A4B]/20 bg-zinc-900 py-2 lg:hidden">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('dashboard') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 7l-2-2m2 2l2-2m-7 2l2-2M7 7l2-2m0 7l-2-2m2 2l2-2M13 12l2-2m0 7l-2-2m2 2l2-2m-7 2l2-2"/></svg>
                <span class="text-xs">Tableau de Bord</span>
            </a>
            @hasanyrole('manager|secretaire|assistante|cadcam')
            <a href="{{ route('travaux.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('travaux.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="text-xs">Travaux</span>
            </a>
            <a href="{{ route('calendrier.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('calendrier.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs">Calendrier</span>
            </a>
            <a href="{{ route('chat.index') }}" class="relative flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('chat.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <span class="relative">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    @if(($unreadChatCount ?? 0) > 0)
                    <span class="absolute -right-1 -top-1 flex h-3.5 min-w-3.5 items-center justify-center rounded-full bg-red-500 px-0.5 text-[9px] font-bold leading-none text-white">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
                    @endif
                </span>
                <span class="text-xs">Chat</span>
            </a>
            @hasanyrole('manager|secretaire')
            <a href="{{ route('clients.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('clients.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-xs">Clients</span>
            </a>
            <a href="{{ route('factures.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('factures.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-xs">Facturation</span>
            </a>
            <a href="{{ route('doc.situations.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('doc.situations.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm12-10a2 2 0 01-2 2h-2a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>
                <span class="text-xs">Situations</span>
            </a>
            @endhasanyrole
            @hasanyrole('manager|secretaire|cadcam')
            <a href="{{ route('stock.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('stock.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span class="text-xs">Stock</span>
            </a>
            @endhasanyrole
            @hasanyrole('manager|secretaire|assistante')
            <a href="{{ route('caisse.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('caisse.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2h-2m-4-1V9a2 2 0 012-2h2a2 2 0 012 2v1m-4 4h10"/></svg>
                <span class="text-xs">Caisse</span>
            </a>
            @endhasanyrole
            @endhasanyrole
            @hasanyrole('manager|secretaire')
            <a href="{{ route('prestations.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('prestations.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs">Prestations</span>
            </a>
            @endhasanyrole
            @role('manager')
            <a href="{{ route('parametres.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('parametres.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-xs">Paramètres</span>
            </a>
            <a href="{{ route('users.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 {{ request()->routeIs('users.*') ? 'bg-[#967A4B]/20 text-[#967A4B]' : 'text-zinc-500 hover:text-zinc-300' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="text-xs">Utilisateurs</span>
            </a>
            @endrole
            <form method="POST" action="{{ route('logout') }}" class="flex flex-col items-center gap-1">
                @csrf
                <button type="submit" class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 text-zinc-500 hover:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="text-xs">Déconnexion</span>
                </button>
            </form>
        </nav>

        {{-- Team chat: floating link (hidden on chat page so it doesn't cover the send button) --}}
        @hasanyrole('manager|secretaire|assistante|cadcam')
        @unless(request()->routeIs('chat.*'))
        <a href="{{ route('chat.index') }}" class="fixed bottom-20 right-4 z-40 flex h-14 w-14 items-center justify-center rounded-full border-2 border-[#967A4B]/60 bg-zinc-900 shadow-lg text-[#967A4B] no-print transition hover:bg-[#967A4B]/20 hover:border-[#967A4B] sm:bottom-6 sm:right-6" title="Chat équipe">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            @if(($unreadChatCount ?? 0) > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 text-[11px] font-bold leading-none text-white shadow ring-2 ring-zinc-900">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
            @endif
        </a>
        @endunless
        @endhasanyrole
    </div>
</x-layouts.app>
