# MdSmile - Laboratoire Dentaire

Système de gestion pour centre de création dentaire — Dashboard admin, suivi travaux, clients, facturation, caisse, stock, et plus.

## Stack Technique

- **Laravel 12** + MySQL
- **Livewire 4** — Composants dynamiques
- **Tailwind CSS 4** — Thème gold & black
- **Spatie Laravel Permission** — Rôles (Admin, Manager, Secrétaire, Assistante, Caisse, CAD/CAM)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configurer DB dans .env (MySQL)
php artisan migrate
npm install && npm run build
php artisan serve
```

## Démarrage rapide

Les pages chargent le CSS/JS via **Vite**. Sans assets compilés, le navigateur attend le serveur Vite et la page reste blanche plusieurs minutes.

**Option A — Une seule commande (recommandé)**  
Après un premier `npm run build`, vous pouvez lancer uniquement Laravel :

```bash
npm run build   # une fois après clone/npm install
php artisan serve
# Ouvrir http://localhost:8000
```

**Option B — Développement avec rechargement à chaud**  
Lancer Laravel et le serveur Vite en parallèle (deux terminaux, ou `composer dev` si configuré) :

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

## Structure prévue

- **Dashboard** — KPIs, stats, graphiques
- **Travaux** — Ordres meds, CAD/CAM, suivi
- **Clients** — Patients, consultations
- **Facturation** — Factures (comptabilisée / non-comptabilisée)
- **Caisse** — 3 types (espèces, CB, virement)
- **Stock** — Matériaux, alertes
- **Fournisseurs** — Gestion fournisseurs

## Couleurs (Gold & Black)

- Background: `zinc-950`, `zinc-900`
- Accent: `amber-400`, `amber-500`
- Bordures: `amber-900/40`
