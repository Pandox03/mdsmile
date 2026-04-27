<x-layouts.app :title="$title ?? 'MdSmile'">
    <div class="min-h-screen flex flex-col">
        {{-- Header --}}
        <header class="sticky top-0 z-50 border-b border-amber-900/30 bg-zinc-900/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold text-amber-400">MdSmile</span>
                    <span class="hidden text-sm text-zinc-500 sm:inline">— Gestion Laboratoire Dentaire</span>
                </div>
                <div class="flex items-center gap-4">
                    @php
                        $navSuggestions = [
                            ['label' => 'Tableau de bord', 'url' => route('dashboard')],
                            ['label' => 'Travaux', 'url' => route('travaux.index')],
                            ['label' => 'Clients', 'url' => route('clients.index')],
                            ['label' => 'Factures', 'url' => route('factures.index')],
                            ['label' => 'Stock', 'url' => route('stock.index')],
                            ['label' => 'Caisse', 'url' => route('caisse.index')],
                            ['label' => 'Paramètres', 'url' => route('parametres.index')],
                            ['label' => 'Journaux', 'url' => route('logs.index')],
                        ];
                        if (auth()->user()->hasRole('manager')) {
                            $navSuggestions[] = ['label' => 'Utilisateurs', 'url' => route('users.index')];
                        }
                    @endphp
                    <script type="application/json" id="header-nav-suggestions-component">{!! json_encode($navSuggestions) !!}</script>
                    <script>
                    (function() {
                        function register() {
                            Alpine.data('headerSearchComponent', function() {
                                var items = [];
                                try {
                                    var el = document.getElementById('header-nav-suggestions-component');
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
                    <div class="relative w-48 sm:w-64" x-data="headerSearchComponent()" @click.outside="open = false">
                        <div class="relative flex items-center">
                            <input type="text"
                                   x-model="query"
                                   @focus="open = true"
                                   @keydown.escape="open = false"
                                   placeholder="Recherche (ex: fa → Factures)"
                                   class="w-full rounded-lg border border-amber-900/50 bg-zinc-800/50 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:border-amber-500/50 focus:outline-none focus:ring-1 focus:ring-amber-500/30"
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
                                   class="block border-b border-zinc-800 px-4 py-2.5 text-sm text-zinc-200 hover:bg-amber-500/20 hover:text-amber-400 first:rounded-t-lg last:border-0"
                                   x-text="item.label"></a>
                            </template>
                        </div>
                    </div>
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-lg border border-amber-900/30 bg-zinc-800/50 px-3 py-2 transition hover:border-amber-500/40 hover:bg-zinc-800" title="Mon profil">
                        <div class="h-8 w-8 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400 text-sm font-medium">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
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
            <aside class="hidden w-64 flex-shrink-0 border-r border-amber-900/20 bg-zinc-900/50 lg:block">
                <nav class="space-y-1 p-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Tableau de Bord</span>
                    </a>
                    <a href="{{ route('travaux.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('travaux.*') ? 'border border-amber-500/30 bg-amber-500/10 text-amber-400' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Travaux</span>
                    </a>
                    <a href="{{ route('clients.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('clients.*') ? 'border border-amber-500/30 bg-amber-500/10 text-amber-400' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Clients</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-lg px-4 py-3 text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Facturation</span>
                    </a>
                    <a href="{{ route('stock.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('stock.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Stock</span>
                    </a>
                    <a href="{{ route('parametres.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('parametres.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Paramètres</span>
                    </a>
                    <a href="{{ route('logs.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 {{ request()->routeIs('logs.*') ? 'border border-[#967A4B]/50 bg-[#967A4B]/10 text-[#967A4B]' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Journaux</span>
                    </a>
                    <hr class="my-4 border-amber-900/20">
                    <a href="#" class="flex items-center gap-3 rounded-lg px-4 py-3 text-zinc-500 hover:bg-red-500/10 hover:text-red-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Déconnexion</span>
                    </a>
                </nav>
            </aside>

            {{-- Main content --}}
            <main class="flex-1 overflow-auto p-6">
                {{ $slot }}
            </main>
        </div>

        {{-- Mobile bottom nav --}}
        <nav class="flex justify-around border-t border-amber-900/20 bg-zinc-900 py-2 lg:hidden">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 rounded-lg px-4 py-2 text-amber-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                <span class="text-xs">Tableau de Bord</span>
            </a>
            <a href="{{ route('travaux.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-4 py-2 {{ request()->routeIs('travaux.*') ? 'text-amber-400' : 'text-zinc-500' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="text-xs">Travaux</span>
            </a>
            <a href="{{ route('clients.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-4 py-2 {{ request()->routeIs('clients.*') ? 'text-amber-400' : 'text-zinc-500' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-xs">Clients</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-1 rounded-lg px-4 py-2 text-zinc-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-xs">Facturation</span>
            </a>
            <a href="{{ route('stock.index') }}" class="flex flex-col items-center gap-1 rounded-lg px-4 py-2 {{ request()->routeIs('stock.*') ? 'text-amber-400' : 'text-zinc-500' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span class="text-xs">Stock</span>
            </a>
        </nav>
    </div>
</x-layouts.app>
