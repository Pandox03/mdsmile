<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Situation {{ $doc->name }} - {{ $periodLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.45; padding: 16mm 18mm; background: #fff; }
        .header { display: table; width: 100%; margin-bottom: 14px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .logo img { max-height: 64px; max-width: 150px; display: block; }
        .title-block { text-align: center; background: #d4a574; color: #000; padding: 11px 14px; margin-bottom: 10px; font-size: 15px; font-weight: bold; letter-spacing: 0.02em; }
        .meta-line { margin-bottom: 8px; font-size: 11px; color: #333; }
        .note { margin-bottom: 14px; font-size: 9.5px; color: #555; line-height: 1.35; border-left: 3px solid #d4a574; padding: 8px 10px; background: #faf8f5; }

        /* Récapitulatif */
        .recap { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; }
        .recap td { padding: 9px 12px; border: 1px solid #5c5c5c; font-size: 11px; vertical-align: middle; }
        .recap td.label { width: 62%; }
        .recap td.amount { text-align: right; font-weight: 600; white-space: nowrap; width: 38%; }
        .recap .recap-title { background: #d4a574; font-weight: bold; font-size: 12px; text-align: center; padding: 11px; border: 1px solid #000; }
        .recap .recap-sub { background: #ede5d8; font-weight: bold; font-size: 10px; color: #222; }
        .recap .recap-divider td { padding: 4px 12px; border: 1px solid #999; background: #e8e0d4; font-size: 9.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        .recap .recap-muted { color: #444; font-weight: normal; }
        .recap .recap-final .label,
        .recap .recap-final .amount { background: #d4a574; font-weight: bold; font-size: 12px; border: 1px solid #000; }

        .section-title { font-size: 11px; font-weight: bold; margin: 18px 0 8px; color: #000; padding-bottom: 4px; border-bottom: 2px solid #d4a574; }

        table.detail { width: 100%; border-collapse: collapse; margin-bottom: 8px; table-layout: fixed; }
        table.detail th { font-size: 9.5px; font-weight: bold; padding: 9px 10px; text-align: left; border: 1px solid #000; background: #d4a574; color: #000; }
        table.detail th.amount { text-align: right; }
        table.detail td { padding: 7px 10px; border: 1px solid #444; font-size: 10.5px; vertical-align: top; }
        table.detail td.amount { text-align: right; white-space: nowrap; }
        /* Pas de rowspan : DomPDF décale les colonnes après un saut de page. Texte long = retour à la ligne. */
        table.detail td.nature-cell { word-wrap: break-word; overflow-wrap: break-word; word-break: normal; hyphens: auto; }
        .total-row { font-weight: bold; background: #f0ebe4; }
        .total-row td { border: 1px solid #000; padding: 10px 10px; }
        .total-row .total-cell { background: #d4a574; color: #000; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if(isset($logoPath) && file_exists($logoPath))
            <div class="logo">
                <img src="{{ $logoPath }}" alt="{{ $labName ?? 'Lab' }}">
            </div>
            @else
            <span style="font-size: 17px; font-weight: bold;">{{ $labName ?? 'MD Smile' }}</span>
            @endif
        </div>
        <div class="header-right">
            @if(isset($logoPath) && file_exists($logoPath))
            <div class="logo" style="margin-left: auto;">
                <img src="{{ $logoPath }}" alt="{{ $labName ?? 'Lab' }}" style="max-height: 48px;">
            </div>
            @endif
        </div>
    </div>

    <div class="title-block">LABORATOIRE DE PROTHÈSE DENTAIRE</div>

    <p class="meta-line"><strong>{{ $labVille ?? 'Casablanca' }}</strong> — Situation : {{ $periodLabel }}</p>

    <p class="note">Les montants travaux excluent les statuts Annulé et À refaire (0&nbsp;DHS). Les encaissements situation sont indépendants des factures ; la date indiquée est la date du paiement (saisie manuelle).</p>

    <table class="recap">
        <tr>
            <td colspan="2" class="recap-title">Récapitulatif — Dr {{ $doc->name }}</td>
        </tr>
        <tr>
            <td colspan="2" class="recap-sub">Période analysée : {{ $periodLabel }}</td>
        </tr>
        <tr>
            <td class="label">Reste avant période (report)</td>
            <td class="amount">{{ number_format((float) $carryover, 0, ',', ' ') }} DHS</td>
        </tr>
        <tr>
            <td class="label">Travaux de la période</td>
            <td class="amount">{{ number_format((float) $travauxPeriodTotal, 0, ',', ' ') }} DHS</td>
        </tr>
        <tr>
            <td class="label recap-muted">Total dû (report + travaux)</td>
            <td class="amount">{{ number_format((float) $situationTotal, 0, ',', ' ') }} DHS</td>
        </tr>
        <tr>
            <td class="label">Encaissements enregistrés sur cette période</td>
            <td class="amount">{{ number_format((float) $montantRecuPeriode, 0, ',', ' ') }} DHS</td>
        </tr>
        <tr class="recap-final">
            <td class="label">Reste à payer (fin de période)</td>
            <td class="amount">{{ number_format((float) $soldeFinPeriode, 0, ',', ' ') }} DHS</td>
        </tr>
        <tr>
            <td colspan="2" class="recap-divider">Encaissements du mois calendaire en cours</td>
        </tr>
        <tr>
            <td class="label">Total encaissé en {{ ucfirst($moisCourantLibelle ?? '') }} <span class="recap-muted">(somme des paiements dont la date tombe dans ce mois)</span></td>
            <td class="amount">{{ number_format((float) ($montantRecuMoisCourant ?? 0), 0, ',', ' ') }} DHS</td>
        </tr>
    </table>

    <p class="section-title">Détail des travaux (entrées sur la période)</p>

    <table class="detail">
        <thead>
            <tr>
                <th>PATIENT</th>
                <th>N° FICHE</th>
                <th>NATURE DE PROTHÈSE</th>
                <th class="amount">MONTANT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            @foreach($group['lines'] as $i => $line)
            <tr>
                <td style="width: 18%;">{{ $group['patient'] }}</td>
                <td style="width: 12%;">{{ $group['numero_fiche'] !== '' ? $group['numero_fiche'] : '—' }}</td>
                <td class="nature-cell" style="width: 50%;">{{ $line['nature'] }}</td>
                <td class="amount" style="width: 20%;">{{ $i === 0 ? number_format((float) $group['amount'], 0, ',', ' ') : ' ' }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
        @if(count($groups) === 0)
        <tbody>
            <tr>
                <td colspan="4" style="text-align: center; color: #666; font-style: italic;">Aucun travail sur cette période.</td>
            </tr>
        </tbody>
        @endif
    </table>

</body>
</html>
