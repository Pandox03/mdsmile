<?php

namespace App\Providers;

use App\Models\TeamMessage;
use App\Models\Travail;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (! auth()->check() || ! auth()->user()->hasAnyRole(['manager', 'secretaire', 'assistante', 'cadcam'])) {
                $view->with('notificationCount', 0);
                $view->with('notificationTravaux', collect());
                $view->with('unreadChatCount', 0);

                return;
            }

            $user = auth()->user();
            $lastRead = $user->last_chat_read_at ?? now()->subYears(10);
            $unreadChatCount = TeamMessage::where('created_at', '>', $lastRead)
                ->where('user_id', '!=', $user->id)
                ->count();
            $view->with('unreadChatCount', $unreadChatCount);

            $today = Carbon::today();
            $tomorrow = Carbon::tomorrow();

            $notificationTravaux = Travail::with('doc')
                ->where('statut', '!=', Travail::STATUT_ANNULE)
                ->where(function ($q) use ($today, $tomorrow) {
                    $q->where(function ($q2) use ($today, $tomorrow) {
                        $q2->whereDate('date_livraison', '>=', $today)
                            ->whereDate('date_livraison', '<=', $tomorrow);
                    })->orWhere(function ($q2) use ($today, $tomorrow) {
                        $q2->whereDate('date_essiage', '>=', $today)
                            ->whereDate('date_essiage', '<=', $tomorrow);
                    });
                })
                ->orderByRaw('COALESCE(date_livraison, date_essiage) ASC')
                ->get()
                ->map(function ($t) {
                    $deadlines = [];
                    if ($t->date_livraison && ($t->date_livraison->isToday() || $t->date_livraison->isTomorrow())) {
                        $deadlines[] = 'Livraison ' . $t->date_livraison->format('d/m/Y');
                    }
                    if ($t->date_essiage && ($t->date_essiage->isToday() || $t->date_essiage->isTomorrow())) {
                        $deadlines[] = 'Essiage ' . $t->date_essiage->format('d/m/Y');
                    }
                    return [
                        'id' => $t->id,
                        'patient' => $t->patient,
                        'reference' => $t->reference,
                        'type_travail_display' => $t->type_travail_display,
                        'doc_name' => $t->doc->name ?? $t->dentiste,
                        'deadlines' => $deadlines,
                        'url' => route('travaux.show', $t),
                    ];
                });

            $view->with('notificationTravaux', $notificationTravaux);
            $view->with('notificationCount', $notificationTravaux->count());
        });
    }
}
