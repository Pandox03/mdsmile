<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de caisse</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; padding: 18mm; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 16px 0 6px; }
        .subtitle { font-size: 11px; color: #555; margin-bottom: 12px; }
        .summary { margin-bottom: 16px; }
        .summary-item { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #333; padding: 6px 8px; font-size: 10px; }
        th { background: #f0f0f0; text-align: left; }
        td.amount { text-align: right; }
        .section { margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Rapport de caisse</h1>
    <div class="subtitle">
        @if($dateFrom || $dateTo)
            Période :
            @if($dateFrom) du {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} @endif
            @if($dateTo) au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }} @endif
        @else
            Période : tous les mouvements
        @endif
    </div>

    <div class="summary">
        <div class="summary-item"><strong>Total entrées :</strong> {{ number_format($totalEntrees, 2, ',', ' ') }} DHS</div>
        <div class="summary-item"><strong>Total sorties :</strong> {{ number_format($totalSorties, 2, ',', ' ') }} DHS</div>
        <div class="summary-item"><strong>Solde :</strong> {{ number_format($solde, 2, ',', ' ') }} DHS</div>
    </div>

    <div class="section">
        <h2>Entrées</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libellé / Facture</th>
                    <th class="amount">Montant (DHS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entrees as $e)
                    <tr>
                        <td>{{ $e->date_mouvement->format('d/m/Y') }}</td>
                        <td>
                            @if($e->facture)
                                {{ $e->facture->numero }}
                                @if($e->description) — {{ $e->description }} @endif
                            @else
                                {{ $e->description ?? '—' }}
                            @endif
                        </td>
                        <td class="amount">+ {{ number_format($e->montant, 2, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucune entrée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Sorties</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th class="amount">Montant (DHS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sorties as $s)
                    <tr>
                        <td>{{ $s->date_mouvement->format('d/m/Y') }}</td>
                        <td>{{ $s->description ?? '—' }}</td>
                        <td class="amount">− {{ number_format($s->montant, 2, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucune sortie.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>

