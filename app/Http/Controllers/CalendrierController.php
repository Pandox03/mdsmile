<?php

namespace App\Http\Controllers;

use App\Models\Travail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendrierController extends Controller
{
    private const JOURS = ['lu', 'ma', 'me', 'je', 've', 'sa', 'di'];

    public function index(Request $request): View
    {
        $monthInput = $request->get('month', now()->format('Y-m'));
        $dateInput = $request->get('date', now()->format('Y-m-d'));

        $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $selected = Carbon::parse($dateInput)->startOfDay();

        $weeks = $this->buildCalendarWeeks($month);
        $daysWithTravaux = $this->daysWithTravauxInMonth($month);

        $dateStr = $selected->format('Y-m-d');
        $travauxDuJour = Travail::with('doc')
            ->where('statut', '!=', Travail::STATUT_ANNULE)
            ->where(function ($q) use ($selected) {
                $q->whereDate('date_entree', $selected)
                    ->orWhereDate('date_livraison', $selected)
                    ->orWhereDate('date_essiage', $selected);
            })
            ->orderByRaw('COALESCE(date_entree, date_essiage, date_livraison) ASC')
            ->get()
            ->map(function ($t) use ($dateStr) {
                $types = [];
                if ($t->date_entree && $t->date_entree->format('Y-m-d') === $dateStr) {
                    $types[] = 'Réception';
                }
                if ($t->date_livraison && $t->date_livraison->format('Y-m-d') === $dateStr) {
                    $types[] = 'Livraison';
                }
                if ($t->date_essiage && $t->date_essiage->format('Y-m-d') === $dateStr) {
                    $types[] = 'Essiage';
                }
                return [
                    'travail' => $t,
                    'types' => $types,
                ];
            });

        return view('calendrier.index', [
            'title' => 'Calendrier — ' . config('app.name'),
            'month' => $month,
            'selected' => $selected,
            'weeks' => $weeks,
            'daysWithTravaux' => $daysWithTravaux,
            'travauxDuJour' => $travauxDuJour,
            'statutLabels' => Travail::statutLabels(),
            'jourNoms' => self::JOURS,
        ]);
    }

    private function buildCalendarWeeks(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $start->locale('fr');
        $firstWeekday = $start->dayOfWeekIso - 1;
        $prevMonth = $month->copy()->subMonth();
        $prevEnd = $prevMonth->copy()->endOfMonth();

        $weeks = [];
        $week = [];
        for ($i = 0; $i < $firstWeekday; $i++) {
            $d = $prevEnd->copy()->subDays($firstWeekday - 1 - $i);
            $weeks[0][] = [
                'date' => $d,
                'day' => $d->day,
                'isCurrentMonth' => false,
                'dateStr' => $d->format('Y-m-d'),
            ];
        }
        $day = $start->copy();
        $w = 0;
        if (count($weeks) === 0) {
            $weeks[0] = [];
        }
        while ($day->lte($end)) {
            $weeks[$w][] = [
                'date' => $day->copy(),
                'day' => $day->day,
                'isCurrentMonth' => true,
                'dateStr' => $day->format('Y-m-d'),
            ];
            $day->addDay();
            if (count($weeks[$w]) === 7) {
                $w++;
                $weeks[$w] = [];
            }
        }
        $nextMonth = $month->copy()->addMonth();
        $nextStart = $nextMonth->copy()->startOfMonth();
        $lastWeek = &$weeks[count($weeks) - 1];
        while (count($lastWeek) < 7) {
            $lastWeek[] = [
                'date' => $nextStart->copy(),
                'day' => $nextStart->day,
                'isCurrentMonth' => false,
                'dateStr' => $nextStart->format('Y-m-d'),
            ];
            $nextStart->addDay();
        }

        return array_values($weeks);
    }

    private function daysWithTravauxInMonth(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth()->format('Y-m-d');
        $end = $month->copy()->endOfMonth()->format('Y-m-d');

        $dates = Travail::where('statut', '!=', Travail::STATUT_ANNULE)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('date_entree', [$start, $end])
                    ->orWhereBetween('date_livraison', [$start, $end])
                    ->orWhereBetween('date_essiage', [$start, $end]);
            })
            ->get()
            ->flatMap(function ($t) {
                $d = [];
                if ($t->date_entree) {
                    $d[] = $t->date_entree->format('Y-m-d');
                }
                if ($t->date_livraison) {
                    $d[] = $t->date_livraison->format('Y-m-d');
                }
                if ($t->date_essiage) {
                    $d[] = $t->date_essiage->format('Y-m-d');
                }
                return $d;
            })
            ->unique()
            ->values()
            ->all();

        return array_flip($dates);
    }
}
