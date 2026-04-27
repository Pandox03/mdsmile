<x-layouts.auth :title="'Connexion - ' . config('app.name')">
    <div class="rounded-xl border border-[#967A4B]/30 bg-zinc-900/90 p-8 shadow-xl backdrop-blur">
        <h1 class="mb-8 text-2xl font-bold text-[#967A4B]">Connexion</h1>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-400">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-zinc-300">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="votre@email.com"
                    class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-3 text-zinc-100 placeholder-zinc-500 transition focus:border-[#967A4B] focus:outline-none focus:ring-2 focus:ring-[#967A4B]/30" />
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-zinc-300">Mot de passe</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                    class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-3 text-zinc-100 placeholder-zinc-500 transition focus:border-[#967A4B] focus:outline-none focus:ring-2 focus:ring-[#967A4B]/30" />
            </div>

            <div class="flex items-center gap-2">
                <input id="remember" type="checkbox" name="remember"
                    class="auth-checkbox h-4 w-4 shrink-0 cursor-pointer rounded border-zinc-600 bg-zinc-800 focus:ring-2 focus:ring-[#967A4B]/50 focus:ring-offset-0 focus:ring-offset-transparent" />
                <label for="remember" class="cursor-pointer text-sm text-zinc-400">Se souvenir de moi</label>
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-[#967A4B] px-4 py-3 font-semibold text-black shadow-md transition hover:bg-[#B8986B] active:scale-[0.98]">
                Connexion
            </button>
        </form>
    </div>
</x-layouts.auth>
