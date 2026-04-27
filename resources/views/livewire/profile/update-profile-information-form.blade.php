<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-[#967A4B]">
            {{ __('Informations du profil') }}
        </h2>
        <p class="mt-1 text-sm text-zinc-500">
            {{ __("Modifiez le nom et l'adresse email de votre compte.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="space-y-6">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-zinc-400">{{ __('Nom') }}</label>
            <input wire:model="name" id="name" name="name" type="text" required autofocus autocomplete="name"
                class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" />
            @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-zinc-400">{{ __('Email') }}</label>
            <input wire:model="email" id="email" name="email" type="email" required autocomplete="username"
                class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" />
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="text-sm text-zinc-400">
                        {{ __('Votre adresse email n\'est pas vérifiée.') }}
                        <button wire:click.prevent="sendVerification" type="button" class="text-[#967A4B] hover:underline">
                            {{ __('Cliquez ici pour renvoyer l\'email de vérification.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-400">
                            {{ __('Un nouveau lien de vérification a été envoyé à votre adresse email.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-5 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                {{ __('Enregistrer') }}
            </button>
            <span wire:loading wire:target="updateProfileInformation" class="text-sm text-zinc-500">Enregistrement…</span>
            <div x-data="{ shown: false }" x-init="Livewire.on('profile-updated', () => { shown = true; setTimeout(() => shown = false, 3000) })" x-show="shown" x-transition class="text-sm text-emerald-400" style="display: none;">{{ __('Enregistré.') }}</div>
        </div>
    </form>
</section>
