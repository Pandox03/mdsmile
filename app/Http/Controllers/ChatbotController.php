<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Role-based knowledge: each item has 'roles' (allowed roles), 'keywords' (triggers), 'response' (HTML).
     * Links use {{route_name}} placeholder replaced at runtime.
     */
    protected function knowledge(): array
    {
        $r = fn (string $name, $params = null) => $params ? route($name, $params) : route($name);

        return [
            // —— Tableau de bord ——
            [
                'roles' => ['manager', 'secretaire', 'assistante', 'cadcam'],
                'keywords' => ['tableau de bord', 'dashboard', 'accueil'],
                'response' => '<p><strong>Tableau de bord</strong></p><p>Cliquez sur "Tableau de Bord" dans le menu (ou utilisez la recherche en haut). Vous y verrez des indicateurs et les derniers travaux selon votre rôle.</p><p><a href="' . $r('dashboard') . '" class="text-[#967A4B] underline">Aller au tableau de bord</a></p>',
            ],
            // —— Travaux (tous les rôles qui y ont accès) ——
            [
                'roles' => ['manager', 'secretaire', 'assistante', 'cadcam'],
                'keywords' => ['travaux', 'travail', 'liste des travaux', 'voir les travaux', 'comment voir', 'où sont les travaux'],
                'response' => '<p><strong>Consulter les travaux</strong></p><p>Menu → <strong>Travaux</strong>. Vous verrez la liste (dentiste, patient, type, dates, statut). Cliquez sur une ligne pour ouvrir le détail.</p><p><a href="' . $r('travaux.index') . '" class="text-[#967A4B] underline">Ouvrir les travaux</a></p>',
            ],
            [
                'roles' => ['manager', 'secretaire', 'assistante'],
                'keywords' => ['créer un travail', 'ajouter un travail', 'nouveau travail', 'créer travail'],
                'response' => '<p><strong>Créer un travail</strong></p><ol class="list-decimal list-inside space-y-1 mt-2"><li>Allez dans <strong>Travaux</strong>.</li><li>Cliquez sur le bouton <strong>"Créer un travail"</strong> en haut à droite.</li><li>Renseignez le patient, le dentiste (ou créez-en un), le type de travail, les dates.</li><li>Associez les dents et matériaux sur le schéma si besoin.</li><li>Enregistrez.</li></ol><p><a href="' . $r('travaux.create') . '" class="text-[#967A4B] underline">Créer un travail</a></p>',
            ],
            [
                'roles' => ['manager', 'secretaire', 'assistante'],
                'keywords' => ['modifier un travail', 'éditer travail', 'changer un travail'],
                'response' => '<p><strong>Modifier un travail</strong></p><p>Ouvrez le travail (cliquez sur la ligne dans la liste), puis cliquez sur le bouton <strong>"Modifier"</strong>. Changez les champs souhaités et enregistrez.</p>',
            ],
            [
                'roles' => ['manager', 'secretaire', 'assistante'],
                'keywords' => ['statut travail', 'changer statut', 'en attente', 'en cours', 'terminé'],
                'response' => '<p><strong>Changer le statut d\'un travail</strong></p><p>Dans la liste des travaux, utilisez le menu déroulant dans la colonne <strong>Actions</strong> pour choisir : En attente, En cours, Terminé, Annulé. Ou ouvrez la fiche détail et changez le statut en haut à droite.</p>',
            ],
            [
                'roles' => ['manager', 'secretaire', 'cadcam'],
                'keywords' => ['détail travail', 'fiche travail', 'voir détail', 'imprimer travail'],
                'response' => '<p><strong>Voir le détail d\'un travail</strong></p><p>Cliquez sur une ligne dans la liste des travaux. Sur la fiche vous pouvez imprimer avec le bouton <strong>Imprimer</strong>.</p>',
            ],
            // —— Clients (manager, secretaire) ——
            [
                'roles' => ['manager', 'secretaire'],
                'keywords' => ['clients', 'client', 'dentiste', 'liste clients', 'ajouter client', 'créer client'],
                'response' => '<p><strong>Gestion des clients (dentistes)</strong></p><p>Menu → <strong>Clients</strong>. Vous pouvez voir la liste, ajouter un client (dentiste), modifier ou consulter une fiche.</p><p><a href="' . $r('clients.index') . '" class="text-[#967A4B] underline">Ouvrir les clients</a></p>',
            ],
            // —— Factures (manager, secretaire) ——
            [
                'roles' => ['manager', 'secretaire'],
                'keywords' => ['facture', 'factures', 'facturation', 'créer facture', 'payer facture', 'pdf facture'],
                'response' => '<p><strong>Facturation</strong></p><p>Menu → <strong>Facturation</strong>. Liste des factures avec filtres. Pour créer une facture : <strong>Facturation</strong> → bouton <strong>Créer une facture</strong>, sélectionnez les travaux et validez. Pour télécharger le PDF ou marquer comme payée : ouvrez la facture puis utilisez les boutons prévus.</p><p><a href="' . $r('factures.index') . '" class="text-[#967A4B] underline">Ouvrir les factures</a></p>',
            ],
            // —— Stock (manager, secretaire) ——
            [
                'roles' => ['manager', 'secretaire'],
                'keywords' => ['stock', 'matériau', 'matériaux', 'fournisseur', 'stock faible'],
                'response' => '<p><strong>Stock</strong></p><p>Menu → <strong>Stock</strong>. Deux onglets : <strong>Matériaux</strong> (liste, ajout, édition) et <strong>Fournisseurs</strong>. Les articles en stock faible sont mis en évidence sur la page.</p><p><a href="' . $r('stock.index') . '" class="text-[#967A4B] underline">Ouvrir le stock</a></p>',
            ],
            // —— Caisse (manager, secretaire, assistante) ——
            [
                'roles' => ['manager', 'secretaire', 'assistante'],
                'keywords' => ['caisse', 'encaissement', 'entrée', 'sortie', 'mouvement caisse', 'ajouter entrée', 'ajouter sortie'],
                'response' => '<p><strong>Caisse</strong></p><p>Menu → <strong>Caisse</strong>. Vous voyez les <strong>Entrées</strong> et les <strong>Sorties</strong>. Pour ajouter un mouvement : bouton <strong>Nouveau mouvement</strong>, choisissez Entrée ou Sortie, date, libellé, montant. Vous pouvez modifier une ligne via l’icône crayon.</p><p><a href="' . $r('caisse.index') . '" class="text-[#967A4B] underline">Ouvrir la caisse</a></p>',
            ],
            // —— Paramètres / Utilisateurs / Journaux (manager only) ——
            [
                'roles' => ['manager'],
                'keywords' => ['paramètres', 'parametres', 'réglages', 'configuration'],
                'response' => '<p><strong>Paramètres</strong></p><p>Menu → <strong>Paramètres</strong>. Vous pouvez modifier les réglages généraux de l’application.</p><p><a href="' . $r('parametres.index') . '" class="text-[#967A4B] underline">Ouvrir les paramètres</a></p>',
            ],
            [
                'roles' => ['manager'],
                'keywords' => ['prestations', 'prestation', 'prix', 'tarif', 'tarifs', 'grille tarifaire', 'prix des travaux'],
                'response' => '<p><strong>Prestations (prix des travaux)</strong></p><p>Menu → <strong>Prestations</strong>. Gérez les catégories et les prestations avec leur prix en DH. Prix vide = Sur devis.</p><p><a href="' . $r('prestations.index') . '" class="text-[#967A4B] underline">Ouvrir les prestations</a></p>',
            ],
            [
                'roles' => ['manager'],
                'keywords' => ['utilisateurs', 'utilisateur', 'utilisatrice', 'gérer utilisateurs', 'créer utilisateur', 'rôles'],
                'response' => '<p><strong>Utilisateurs</strong></p><p>Menu → <strong>Utilisateurs</strong>. Liste des comptes. Pour ajouter : <strong>Créer un utilisateur</strong>, renseignez nom, email, mot de passe et rôle (manager, secrétaire, assistante, CAD/CAM).</p><p><a href="' . $r('users.index') . '" class="text-[#967A4B] underline">Ouvrir les utilisateurs</a></p>',
            ],
            [
                'roles' => ['manager'],
                'keywords' => ['journaux', 'journal', 'logs', 'activité', 'historique', 'qui a fait'],
                'response' => '<p><strong>Journaux d’activité</strong></p><p>Menu → <strong>Journaux</strong>. Historique des actions (création, modification, suppression) avec filtres par type (facture, travail, stock, caisse, etc.) et par action.</p><p><a href="' . $r('logs.index') . '" class="text-[#967A4B] underline">Ouvrir les journaux</a></p>',
            ],
            // —— Accès refusé (cadcam: pas factures, clients, stock, caisse) ——
            [
                'roles' => ['cadcam'],
                'keywords' => ['facture', 'factures', 'facturation', 'client', 'clients', 'stock', 'caisse', 'paramètres', 'utilisateurs', 'journaux'],
                'response' => '<p>Cette section n\'est pas accessible avec votre profil <strong>CAD/CAM</strong>.</p><p>Vous avez accès uniquement au <strong>Tableau de bord</strong> et à la liste/détail des <strong>Travaux</strong> (consultation).</p><p><a href="' . $r('travaux.index') . '" class="text-[#967A4B] underline">Voir les travaux</a></p>',
            ],
            // —— Profil ——
            [
                'roles' => ['manager', 'secretaire', 'assistante', 'cadcam'],
                'keywords' => ['profil', 'profile', 'mon compte', 'mot de passe', 'changer mot de passe'],
                'response' => '<p><strong>Mon profil</strong></p><p>Cliquez sur votre nom (ou avatar) en haut à droite → vous arrivez sur la page <strong>Profil</strong>. Vous pouvez modifier vos informations et votre mot de passe.</p><p><a href="' . $r('profile') . '" class="text-[#967A4B] underline">Ouvrir mon profil</a></p>',
            ],
            // —— Recherche / navigation ——
            [
                'roles' => ['manager', 'secretaire', 'assistante', 'cadcam'],
                'keywords' => ['recherche', 'navigation', 'aller à', 'où trouver', 'menu'],
                'response' => '<p><strong>Navigation rapide</strong></p><p>Utilisez la barre de recherche en haut : tapez quelques lettres (ex. "tr" pour Travaux, "ca" pour Caisse). Une liste de sections apparaît ; cliquez pour y accéder. Le menu à gauche (ou en bas sur mobile) affiche uniquement les sections auxquelles vous avez accès.</p>',
            ],
        ];
    }

    /**
     * Fallback message when no topic matches, based on role.
     */
    protected function fallbackForRole(string $role): string
    {
        $dashboard = '<a href="' . route('dashboard') . '" class="text-[#967A4B] underline">Tableau de bord</a>';
        $travaux = '<a href="' . route('travaux.index') . '" class="text-[#967A4B] underline">Travaux</a>';
        $profile = '<a href="' . route('profile') . '" class="text-[#967A4B] underline">Profil</a>';

        $intro = '<p>Je peux vous guider sur les fonctionnalités auxquelles vous avez accès.</p>';
        switch ($role) {
            case 'cadcam':
                return $intro . '<p><strong>Vous avez accès à :</strong> ' . $dashboard . ', ' . $travaux . ' (consultation liste et détail), ' . $profile . '.</p><p>Exemples de questions : "Comment voir les travaux ?", "Où est le tableau de bord ?", "Comment voir le détail d\'un travail ?"</p>';
            case 'assistante':
                return $intro . '<p><strong>Vous avez accès à :</strong> ' . $dashboard . ', ' . $travaux . ' (créer, modifier, pas de suppression), Caisse (créer, modifier), ' . $profile . '.</p><p>Exemples : "Comment créer un travail ?", "Où est la caisse ?", "Comment modifier un mouvement caisse ?"</p>';
            case 'secretaire':
                return $intro . '<p><strong>Vous avez accès à :</strong> Tableau de bord, Travaux, Clients, Facturation, Stock, Caisse, ' . $profile . '.</p><p>Exemples : "Comment créer une facture ?", "Où sont les clients ?", "Comment gérer le stock ?"</p>';
            case 'manager':
                return $intro . '<p><strong>Vous avez accès à tout :</strong> Tableau de bord, Travaux, Clients, Facturation, Stock, Caisse, Paramètres, Prestations, Journaux, Utilisateurs, ' . $profile . '.</p><p>Exemples : "Où sont les prestations ?", "Comment ajouter un utilisateur ?", "Où sont les journaux ?"</p>';
            default:
                return $intro . '<p>Demandez par exemple : "Comment voir les travaux ?" ou "Où est mon profil ?"</p>';
        }
    }

    public function ask(Request $request): JsonResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:500']]);

        $message = Str::lower(trim($request->input('message')));
        $user = $request->user();
        $role = $user->getRoleNames()->first() ?? '';

        $knowledge = $this->knowledge();
        $normalized = $this->normalize($message);

        foreach ($knowledge as $item) {
            if (!in_array($role, $item['roles'], true)) {
                continue;
            }
            foreach ($item['keywords'] as $keyword) {
                if (Str::contains($normalized, $this->normalize($keyword))) {
                    return response()->json([
                        'success' => true,
                        'response' => $item['response'],
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'response' => $this->fallbackForRole($role),
        ]);
    }

    protected function normalize(string $text): string
    {
        $text = Str::lower($text);
        $accents = ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'ù' => 'u', 'û' => 'u', 'ô' => 'o', 'î' => 'i', 'ï' => 'i', 'ü' => 'u', 'ç' => 'c'];
        return strtr($text, $accents);
    }
}
