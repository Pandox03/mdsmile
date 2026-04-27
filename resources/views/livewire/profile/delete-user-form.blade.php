<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-red-400">
            {{ __('Supprimer le compte') }}
        </h2>
        <p class="mt-1 text-sm text-zinc-500">
            {{ __('Une fois votre compte supprimé, toutes les données seront définitivement perdues. Téléchargez ou exportez les informations à conserver avant de continuer.') }}
        </p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="rounded-lg border border-red-500/60 bg-red-500/20 px-4 py-2.5 text-sm font-medium text-red-400 hover:bg-red-500/30"
    >
        {{ __('Supprimer le compte') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable dark>
        <div class="p-6 text-zinc-100">
            <h2 class="text-lg font-semibold text-[#967A4B]">
                {{ __('Supprimer définitivement votre compte ?') }}
            </h2>
            <p class="mt-2 text-sm text-zinc-400">
                {{ __('Toutes les données du compte seront supprimées. Entrez votre mot de passe pour confirmer.') }}
            </p>

            <form wire:submit="deleteUser" class="mt-6 space-y-4">
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-zinc-400">{{ __('Mot de passe') }}</label>
                    <input
                        wire:model="password"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="{{ __('Mot de passe') }}"
                        class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30"
                    />
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        x-on:click="$dispatch('close-modal', 'confirm-user-deletion')"
                        class="rounded-lg border border-zinc-600 px-4 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-800"
                    >
                        {{ __('Annuler') }}
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg border border-red-500/60 bg-red-500/20 px-4 py-2.5 text-sm font-medium text-red-400 hover:bg-red-500/30"
                    >
                        {{ __('Supprimer le compte') }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</section>
