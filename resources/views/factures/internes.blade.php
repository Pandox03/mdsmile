<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails internes — {{ $facture->numero }}</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f0f0f; color: #e4e4e7; padding: 24px; max-width: 700px; margin: 0 auto; }
        h1 { font-size: 18px; color: #967A4B; margin-bottom: 8px; }
        .meta { font-size: 12px; color: #71717a; margin-bottom: 24px; }
        .totals { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .totals div { background: #27272a; border-radius: 8px; padding: 12px; }
        .totals .label { font-size: 11px; color: #71717a; text-transform: uppercase; }
        .totals .value { font-size: 18px; font-weight: 600; }
        .totals .value.non-comptabilise { color: #fbbf24; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 10px 8px; border-bottom: 1px solid #3f3f46; font-size: 11px; color: #a1a1aa; text-transform: uppercase; }
        th:nth-child(3), th:nth-child(4) { text-align: right; }
        td { padding: 10px 8px; border-bottom: 1px solid #27272a; }
        td:nth-child(3), td:nth-child(4) { text-align: right; }
        .note { font-size: 11px; color: #71717a; margin-top: 24px; }
    </style>
</head>
<body>
    <h1>Détails internes (non comptabilisés)</h1>
    <p class="meta">Facture {{ $facture->numero }} — {{ $facture->doc->name }} — {{ $facture->date_facture->format('d/m/Y') }}</p>

    <div class="totals">
        <div>
            <p class="label">Total facture</p>
            <p class="value">{{ number_format($facture->total_facture, 0, ',', ' ') }} DHS</p>
        </div>
        <div>
            <p class="label">Montant comptabilisé (officiel)</p>
            <p class="value">{{ number_format($facture->montant_comptabilise_affiche, 0, ',', ' ') }} DHS</p>
        </div>
        <div>
            <p class="label">Restant (non comptabilisé)</p>
            <p class="value non-comptabilise">{{ number_format($facture->montant_non_comptabilise, 0, ',', ' ') }} DHS</p>
        </div>
    </div>

    <p class="label" style="margin-top: 16px;">Par travail (montant total → comptabilisé / non comptabilisé)</p>
    <table>
        <thead>
            <tr>
                <th>Travail / Patient</th>
                <th class="text-right">Total (DHS)</th>
                <th class="text-right">Comptabilisé (DHS)</th>
                <th class="text-right">Non compt. (DHS)</th>
                <th class="text-right">Sur cette facture (DHS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->travaux as $t)
            @php
                $montantLigne = (float) ($t->pivot->prix_comptabilise ?? $t->prix_dhs);
                $cap = (float) ($t->montant_comptabilise ?? $t->prix_dhs);
                $nonComp = max(0, (float) $t->prix_dhs - $cap);
            @endphp
            <tr>
                <td>{{ $t->type_travail }} · {{ $t->patient }}</td>
                <td class="text-right">{{ number_format($t->prix_dhs, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($cap, 0, ',', ' ') }}</td>
                <td class="text-right" style="color: #fbbf24;">{{ number_format($nonComp, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($montantLigne, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="note">Cette page n’est accessible que par lien direct. Pour chaque travail : total = montant total, comptabilisé = part facturable (définie sur la page Factures), non compt. = reste (interne).</p>
</body>
</html>
