@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
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

    <div class="rounded-xl border border-[#967A4B]/40 bg-zinc-900/80 px-5 py-4">
        <h1 class="text-xl font-bold text-[#967A4B]">Paramètres</h1>
        <p class="mt-1 text-sm text-zinc-400">Informations du laboratoire et options de facturation</p>
    </div>

    @if($errors->any())
    <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-400">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('parametres.update') }}" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Informations du laboratoire</h2>
            <p class="mb-4 text-sm text-zinc-500">Ces informations peuvent être utilisées sur les factures et documents.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Nom du laboratoire</label>
                    <input type="text" name="lab_name" value="{{ old('lab_name', $settings['lab_name'] ?? '') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="Ex. Laboratoire MdSmile">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Téléphone</label>
                    <input type="text" name="lab_phone" value="{{ old('lab_phone', $settings['lab_phone'] ?? '') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Email</label>
                    <input type="email" name="lab_email" value="{{ old('lab_email', $settings['lab_email'] ?? '') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Adresse</label>
                    <textarea name="lab_adresse" rows="3" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">{{ old('lab_adresse', $settings['lab_adresse'] ?? '') }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Siège social (bas de facture)</label>
                    <textarea name="lab_siege_social" rows="2" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="Ex. N°21, RUE CHARLES NICOLES, 6ème étage, Casablanca">{{ old('lab_siege_social', $settings['lab_siege_social'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">ICE (laboratoire)</label>
                    <input type="text" name="lab_ice" value="{{ old('lab_ice', $settings['lab_ice'] ?? '') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="Ex. 003111594000020">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">TP</label>
                    <input type="text" name="lab_tp" value="{{ old('lab_tp', $settings['lab_tp'] ?? '') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">IF</label>
                    <input type="text" name="lab_if" value="{{ old('lab_if', $settings['lab_if'] ?? '') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="—">
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
            <h2 class="mb-4 text-lg font-semibold text-[#967A4B]">Facturation</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Préfixe des numéros de facture</label>
                    <input type="text" name="facture_prefix" value="{{ old('facture_prefix', $settings['facture_prefix'] ?? 'FAC') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="FAC">
                    <p class="mt-1 text-xs text-zinc-500">Ex. FAC → FAC-001, FAC-002…</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Prochain numéro de facture</label>
                    <input type="number" name="facture_prochain_numero" value="{{ old('facture_prochain_numero', $settings['facture_prochain_numero'] ?? '1') }}" min="1" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="1">
                    <p class="mt-1 text-xs text-zinc-500">Numéro attribué à la prochaine facture créée (ex. 1 → FAC-001, 2 → FAC-002).</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Devise</label>
                    <input type="text" name="facture_devise" value="{{ old('facture_devise', $settings['facture_devise'] ?? 'DHS') }}" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="DHS">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-400">Taux TVA (%)</label>
                    <input type="number" name="facture_tva_rate" value="{{ old('facture_tva_rate', $settings['facture_tva_rate'] ?? '20') }}" min="0" max="100" step="0.01" class="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30" placeholder="20">
                    <p class="mt-1 text-xs text-zinc-500">Ex. 20 pour 20%</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="rounded-lg border border-[#967A4B] bg-[#967A4B] px-6 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]">
                Enregistrer les paramètres
            </button>
        </div>
    </form>

    @role('manager')
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
        <h2 class="mb-2 text-lg font-semibold text-[#967A4B]">Utilisateurs</h2>
        <p class="mb-4 text-sm text-zinc-500">Ajouter et gérer les comptes : managers, secrétaires, assistantes, CAD/CAM.</p>
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 px-4 py-2.5 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/20">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Gérer les utilisateurs
        </a>
    </div>
    @endrole
    <div class="rounded-xl border border-[#967A4B]/20 bg-zinc-900/80 p-6">
        <h2 class="mb-2 text-lg font-semibold text-[#967A4B]">Compte utilisateur</h2>
        <p class="mb-4 text-sm text-zinc-500">Modifier votre profil, mot de passe ou supprimer votre compte.</p>
        <a href="{{ route('profile') }}" class="inline-flex items-center gap-2 rounded-lg border border-[#967A4B]/50 bg-[#967A4B]/10 px-4 py-2.5 text-sm font-medium text-[#967A4B] hover:bg-[#967A4B]/20">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Ouvrir mon profil
        </a>
    </div>
</div>
@endsection
