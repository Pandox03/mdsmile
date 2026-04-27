<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-[#967A4B]">
            {{ __('Changer le mot de passe') }}
        </h2>
        <p class="mt-1 text-sm text-zinc-500">
            {{ __('Utilisez un mot de passe long et sûr pour protéger votre compte.') }}
        </p>
    </header>

    <form wire:submit="updatePassword" class="space-y-6">
        <div>
            <label for="update_password_current_password" class="mb-1 block text-sm font-medium text-zinc-400">{{ __('Mot de passe actuel') }}</label>
            <input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" />
            @error('current_password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="mb-1 block text-sm font-medium text-zinc-400">{{ __('Nouveau mot de passe') }}</label>
            <input wire:model="password" id="update_password_password" name="password" type="password" autocomplete="new-password"
                class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" />
            @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="mb-1 block text-sm font-medium text-zinc-400">{{ __('Confirmer le mot de passe') }}</label>
            <input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" />
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                {{ __('Enregistrer') }}
            </button>
            <span wire:loading wire:target="updatePassword" class="text-sm text-zinc-500">Enregistrement…</span>
            <span x-data="{ show: false }" x-on:password-updated.window="show = true; setTimeout(() => show = false, 3000)" x-show="show" x-transition class="text-sm text-emerald-400">{{ __('Enregistré.') }}</span>
        </div>
    </form>
</section>
