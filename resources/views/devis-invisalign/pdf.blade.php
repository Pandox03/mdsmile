<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Devis {{ $devis->numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.45; padding: 16mm 18mm 36mm 18mm; background: #fff; }
        .header { display: table; width: 100%; margin-bottom: 14px; }
        .header-left { display: table-cell; width: 55%; vertical-align: top; }
        .header-right { display: table-cell; width: 45%; text-align: right; vertical-align: top; }
        .logo img { max-height: 64px; max-width: 150px; display: block; }
        .date { font-size: 11px; color: #000; margin-bottom: 4px; }
        .title-block { text-align: center; background: #d4a574; color: #000; padding: 11px 14px; margin-bottom: 10px; font-size: 15px; font-weight: bold; letter-spacing: 0.02em; }
        .doc-block { background: #d4a574; color: #000; padding: 10px 16px; margin-bottom: 8px; font-size: 14px; font-weight: bold; }
        .meta-line { margin-bottom: 10px; font-size: 11px; color: #333; }
        .checks { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .checks td { width: 25%; border: 1px solid #000; background: #ede5d8; padding: 7px 8px; font-size: 9.5px; font-weight: bold; text-align: center; }
        .section-title { font-size: 11px; font-weight: bold; margin: 16px 0 8px; color: #000; padding-bottom: 4px; border-bottom: 2px solid #d4a574; }
        table.detail { width: 100%; border-collapse: collapse; margin-bottom: 8px; table-layout: fixed; }
        table.detail th { font-size: 9.5px; font-weight: bold; padding: 9px 10px; text-align: left; border: 1px solid #000; background: #d4a574; color: #000; }
        table.detail th.amount, table.detail td.amount { text-align: right; white-space: nowrap; }
        table.detail th.qty, table.detail td.qty { text-align: center; white-space: nowrap; }
        table.detail td { padding: 7px 10px; border: 1px solid #444; font-size: 10.5px; vertical-align: top; word-wrap: break-word; }
        .total-row td { border: 1px solid #000; padding: 10px; font-weight: bold; background: #f0ebe4; }
        .total-row .total-cell { background: #d4a574; color: #000; font-size: 11px; }
        .montant-lettres { text-align: center; margin-top: 16px; font-size: 11px; text-decoration: underline; padding: 0 12px; }
        .recap { width: 100%; border-collapse: collapse; margin-top: 8px; page-break-inside: avoid; }
        .recap td { padding: 8px 12px; border: 1px solid #5c5c5c; font-size: 11px; vertical-align: top; }
        .recap .recap-title { background: #d4a574; font-weight: bold; font-size: 12px; text-align: center; padding: 10px; border: 1px solid #000; }
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
            <div class="date">Date : {{ $devis->date_devis?->format('d/m/Y') }}</div>
            <div class="date">Devis N° {{ $devis->numero }}</div>
        </div>
    </div>

    <div class="title-block">DEVIS INVISALIGN</div>
    <div class="doc-block">{{ $devis->client_nom }}</div>
    <p class="meta-line"><strong>Patient :</strong> {{ $devis->patient_nom }}</p>

    <table class="checks">
        <tr>
            <td>MAXILLAIRE : {{ $devis->opt_maxillaire ? 'OUI' : 'NON' }}</td>
            <td>MANDIBULAIRE : {{ $devis->opt_mandibulaire ? 'OUI' : 'NON' }}</td>
            <td>TAQUETS : {{ $devis->taquets ? 'OUI' : 'NON' }}</td>
            <td>STRIPPING : {{ $devis->stripping ? 'OUI' : 'NON' }}</td>
        </tr>
    </table>

    <p class="section-title">Détail du devis</p>
    <table class="detail">
        <thead>
            <tr>
                <th style="width:38%">Désignation</th>
                <th class="qty" style="width:12%">Quantité</th>
                <th style="width:16%">Arcade</th>
                <th class="amount" style="width:17%">Prix unitaire</th>
                <th class="amount" style="width:17%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devis->lignes as $l)
            <tr>
                <td>{{ $l->designation }}</td>
                <td class="qty">{{ rtrim(rtrim(number_format((float) ($l->quantite ?? 1), 2, ',', ' '), '0'), ',') }}</td>
                <td>{{ $l->arcade ?: '—' }}</td>
                <td class="amount">{{ number_format((float) $l->prix_unitaire, 2, ',', ' ') }}</td>
                <td class="amount">{{ number_format((float) $l->total_ttc, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4">TOTAL HT ({{ $devise ?? 'DHS' }})</td>
                <td class="amount total-cell">{{ number_format((float) $devis->total_ttc, 2, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="montant-lettres">
        Arrêté le présent devis à la somme de {{ \App\Helpers\NumberToFrench::toLetters((float) $devis->total_ttc, 'dirhams') }}.
    </div>

    @if($devis->conditions_paiement)
    <p class="section-title">Conditions de paiement</p>
    <table class="recap">
        <tr>
            <td>{!! nl2br(e($devis->conditions_paiement)) !!}</td>
        </tr>
    </table>
    @endif

    @if($devis->banque_ligne || $devis->beneficiaire || $devis->rib || $devis->commandes_email)
    <p class="section-title">Coordonnées bancaires et commandes</p>
    <table class="recap">
        @if($devis->banque_ligne)
        <tr><td>{{ $devis->banque_ligne }}</td></tr>
        @endif
        @if($devis->beneficiaire)
        <tr><td><strong>Bénéficiaire :</strong> {{ $devis->beneficiaire }}</td></tr>
        @endif
        @if($devis->rib)
        <tr><td><strong>RIB :</strong> {{ $devis->rib }}</td></tr>
        @endif
        @if($devis->commandes_email)
        <tr><td><strong>E-mail commandes :</strong> {{ $devis->commandes_email }}</td></tr>
        @endif
    </table>
    @endif

    <div class="footer">
        @if(!empty($footerSociete))
        <div>{!! nl2br(e($footerSociete)) !!}</div>
        @else
        @if($labName ?? null)<div style="font-weight:bold;">{{ $labName }}</div>@endif
        @if($labPhone ?? null)<div>Tél. : {{ $labPhone }}</div>@endif
        @if($labEmail ?? null)<div>Email : {{ $labEmail }}</div>@endif
        @if($labAdresse ?? null)<div>Siège social : {{ $labAdresse }}</div>@endif
        @endif
    </div>
</body>
</html>
