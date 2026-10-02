<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de livraison {{ $bon->numero }}</title>
    <style>
        @page { margin: 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; color: #111; }
        .hdr { width: 100%; margin-bottom: 14px; border-bottom: 2px solid #967A4B; padding-bottom: 10px; }
        .hdr td { vertical-align: top; }
        .logo { max-height: 52px; }
        .lab { font-size: 9pt; color: #333; }
        .right { text-align: right; }
        .title { font-size: 16pt; font-weight: bold; color: #000; }
        .meta { margin-top: 4px; font-size: 10pt; }
        .box { border: 1px solid #444; padding: 10px 12px; margin-bottom: 12px; }
        .box h3 { margin: 0 0 6px 0; font-size: 9pt; text-transform: uppercase; color: #555; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        .row { margin: 3px 0; }
        .label { color: #555; }
        .details { white-space: pre-wrap; line-height: 1.45; margin-top: 4px; }
        .badge { display: inline-block; padding: 3px 10px; border: 1px solid #000; font-weight: bold; font-size: 10pt; }
        .badge-paye { background: #d1fae5; }
        .badge-partiel { background: #fef3c7; }
        .badge-non { background: #fee2e2; }
        .sign { margin-top: 28px; width: 100%; }
        .sign td { width: 50%; vertical-align: top; padding-top: 8px; }
        .sign .line { margin-top: 36px; border-top: 1px solid #333; width: 80%; padding-top: 4px; font-size: 8.5pt; color: #555; }
        .footer { margin-top: 20px; font-size: 8pt; color: #666; border-top: 1px solid #ccc; padding-top: 6px; }
    </style>
</head>
<body>
    <table class="hdr">
        <tr>
            <td style="width:55%">
                @if(isset($logoPath) && file_exists($logoPath))
                <img class="logo" src="{{ $logoPath }}" alt="">
                @endif
                <div style="margin-top:6px;font-weight:bold;">{{ $labName }}</div>
                @if(!empty($labAdresse))<div class="lab">{!! nl2br(e($labAdresse)) !!}</div>@endif
                @if(!empty($labPhone) || !empty($labEmail))
                <div class="lab">
                    @if(!empty($labPhone))Tél. {{ $labPhone }}@endif
                    @if(!empty($labPhone) && !empty($labEmail)) — @endif
                    @if(!empty($labEmail)){{ $labEmail }}@endif
                </div>
                @endif
            </td>
            <td class="right" style="width:45%">
                <div class="title">BON DE LIVRAISON</div>
                <div class="meta"><strong>N°</strong> {{ $bon->numero }}</div>
                <div class="meta"><strong>Date</strong> {{ $bon->date_livraison?->format('d/m/Y') }}</div>
                @if($bon->reference_travail)
                <div class="meta"><strong>Réf. travail</strong> {{ $bon->reference_travail }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="box">
        <h3>Dentiste / Cabinet</h3>
        <div class="row"><span class="label">Nom :</span> <strong>{{ $bon->dentiste_nom }}</strong></div>
        @if($bon->doc)
        @if($bon->doc->phone)<div class="row"><span class="label">Tél :</span> {{ $bon->doc->phone }}</div>@endif
        @if($bon->doc->adresse)<div class="row"><span class="label">Adresse :</span> {{ $bon->doc->adresse }}</div>@endif
        @endif
    </div>

    <div class="box">
        <h3>Patient</h3>
        <div class="row"><span class="label">Nom :</span> <strong>{{ $bon->patient_nom }}</strong></div>
        @if($bon->numero_fiche)<div class="row"><span class="label">N° fiche :</span> {{ $bon->numero_fiche }}</div>@endif
    </div>

    <div class="box">
        <h3>Détail du travail livré</h3>
        @if($bon->dents)<div class="row"><span class="label">Dents :</span> {{ $bon->dents }}</div>@endif
        <div class="details">{{ $bon->details_travail }}</div>
        @if($bon->montant !== null)
        <div class="row" style="margin-top:8px;"><span class="label">Montant :</span> <strong>{{ number_format((float) $bon->montant, 2, ',', ' ') }} {{ $devise }}</strong></div>
        @endif
    </div>

    <div class="box">
        <h3>Statut de paiement</h3>
        @php
            $cls = match($bon->paiement_statut) {
                'paye' => 'badge-paye',
                'partiel' => 'badge-partiel',
                default => 'badge-non',
            };
        @endphp
        <span class="badge {{ $cls }}">{{ $paiementLabel }}</span>
        @if(in_array($bon->paiement_statut, ['paye', 'partiel'], true) && !empty($typePaiementLabel) && $typePaiementLabel !== '—')
        <div class="row" style="margin-top:8px;"><span class="label">Type :</span> <strong>{{ $typePaiementLabel }}</strong></div>
        @endif
    </div>

    @if($bon->observations)
    <div class="box">
        <h3>Observations</h3>
        <div class="details">{{ $bon->observations }}</div>
    </div>
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

    <div class="footer">Document remis avec la prothèse — {{ $labName }} — {{ $bon->numero }}</div>
</body>
</html>
