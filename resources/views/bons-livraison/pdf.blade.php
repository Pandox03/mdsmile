<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bon de livraison {{ $bon->numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.45; padding: 16mm 18mm 32mm 18mm; background: #fff; }
        .header { display: table; width: 100%; margin-bottom: 14px; }
        .header-left { display: table-cell; width: 55%; vertical-align: top; }
        .header-right { display: table-cell; width: 45%; text-align: right; vertical-align: top; }
        .logo img { max-height: 64px; max-width: 150px; display: block; }
        .date { font-size: 11px; color: #000; margin-bottom: 4px; }
        .title-block { text-align: center; background: #d4a574; color: #000; padding: 11px 14px; margin-bottom: 10px; font-size: 15px; font-weight: bold; letter-spacing: 0.02em; }
        .doc-block { background: #d4a574; color: #000; padding: 10px 16px; margin-bottom: 8px; font-size: 14px; font-weight: bold; }
        .meta-line { margin-bottom: 6px; font-size: 11px; color: #333; }
        .section-title { font-size: 11px; font-weight: bold; margin: 16px 0 8px; color: #000; padding-bottom: 4px; border-bottom: 2px solid #d4a574; }
        table.detail { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.detail th { font-size: 9.5px; font-weight: bold; padding: 9px 10px; text-align: left; border: 1px solid #000; background: #d4a574; color: #000; }
        table.detail td { padding: 8px 10px; border: 1px solid #444; font-size: 10.5px; vertical-align: top; }
        .details { white-space: pre-wrap; }
        table.recap { width: 100%; border-collapse: collapse; margin-top: 4px; page-break-inside: avoid; }
        table.recap td { padding: 9px 12px; border: 1px solid #5c5c5c; font-size: 11px; vertical-align: middle; }
        table.recap td.label { width: 62%; }
        table.recap td.amount { text-align: right; font-weight: 600; white-space: nowrap; width: 38%; }
        table.recap .recap-title { background: #d4a574; font-weight: bold; font-size: 12px; text-align: center; padding: 11px; border: 1px solid #000; }
        table.recap .recap-final .label,
        table.recap .recap-final .amount { background: #d4a574; font-weight: bold; font-size: 12px; border: 1px solid #000; }
        .sign { width: 100%; margin-top: 22px; page-break-inside: avoid; }
        .sign td { width: 50%; vertical-align: top; padding: 8px 10px 0 0; font-size: 11px; }
        .sign .line { margin-top: 36px; border-top: 1px solid #000; width: 85%; padding-top: 4px; font-size: 9px; color: #444; }
        .footer { position: fixed; left: 18mm; right: 18mm; bottom: 10mm; padding-top: 8px; border-top: 1px solid #ccc; font-size: 9px; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if(isset($logoPath) && file_exists($logoPath))
            <div class="logo"><img src="{{ $logoPath }}" alt="{{ $labName ?? 'MD Smile' }}"></div>
            @else
            <span style="font-size: 17px; font-weight: bold;">{{ $labName ?? 'MD Smile' }}</span>
            @endif
        </div>
        <div class="header-right">
            <div class="date">Date : {{ $bon->date_livraison?->format('d/m/Y') }}</div>
            <div class="date">Bon N° {{ $bon->numero }}</div>
            @if($bon->reference_travail)
            <div class="date">Réf. travail : {{ $bon->reference_travail }}</div>
            @endif
        </div>
    </div>

    <div class="title-block">BON DE LIVRAISON</div>
    <div class="doc-block">{{ $bon->dentiste_nom }}</div>
    @if($bon->doc && ($bon->doc->phone || $bon->doc->adresse))
    <p class="meta-line">
        @if($bon->doc->phone)Tél. {{ $bon->doc->phone }}@endif
        @if($bon->doc->phone && $bon->doc->adresse) — @endif
        @if($bon->doc->adresse){{ $bon->doc->adresse }}@endif
    </p>
    @endif
    <p class="meta-line"><strong>Patient :</strong> {{ $bon->patient_nom }}@if($bon->numero_fiche) — N° fiche {{ $bon->numero_fiche }}@endif</p>
    @if(!empty($typePaiementLabel) && $typePaiementLabel !== '—')
    <p class="meta-line"><strong>Type de paiement :</strong> {{ $typePaiementLabel }}</p>
    @endif

    <p class="section-title">Détail du travail livré</p>
    <table class="detail">
        <thead>
            <tr>
                <th style="width:22%">Dents</th>
                <th>Nature du travail</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $bon->dents ?: '—' }}</td>
                <td class="details">{{ $bon->details_travail }}</td>
            </tr>
        </tbody>
    </table>

    <table class="recap">
        <tr>
            <td colspan="2" class="recap-title">Paiement</td>
        </tr>
        @if($bon->montant !== null)
        <tr>
            <td class="label">Montant</td>
            <td class="amount">{{ number_format((float) $bon->montant, 2, ',', ' ') }} {{ $devise ?? 'DHS' }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Statut</td>
            <td class="amount">{{ $paiementLabel }}</td>
        </tr>
        <tr class="recap-final">
            <td class="label">Type de paiement</td>
            <td class="amount">{{ (!empty($typePaiementLabel) && $typePaiementLabel !== '—') ? $typePaiementLabel : '—' }}</td>
        </tr>
    </table>

    @if($bon->observations)
    <p class="section-title">Observations</p>
    <table class="detail">
        <tbody>
            <tr><td class="details">{{ $bon->observations }}</td></tr>
        </tbody>
    </table>
    @endif

    <table class="sign">
        <tr>
            <td>
                <strong>Le laboratoire</strong>
                <div class="line">Cachet / Signature</div>
            </td>
            <td>
                <strong>Réception cabinet</strong>
                @if($bon->recu_par)<div style="margin-top:6px;">Reçu par : {{ $bon->recu_par }}</div>@endif
                <div class="line">Nom / Signature / Date</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <div style="font-weight:bold;">{{ $labName ?? 'MD Smile' }} — {{ $bon->numero }}</div>
        @if($labPhone ?? null)<div>Tél. : {{ $labPhone }}</div>@endif
        @if($labEmail ?? null)<div>Email : {{ $labEmail }}</div>@endif
        @if($labAdresse ?? null)<div>{{ $labAdresse }}</div>@endif
        <div>Document remis avec la prothèse</div>
    </div>
</body>
</html>
