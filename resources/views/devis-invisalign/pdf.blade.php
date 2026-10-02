<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Devis Invisalign {{ $devis->numero }}</title>
    <style>
        @page { margin: 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #111; }
        .hdr { width: 100%; margin-bottom: 12px; border-bottom: 1px solid #333; padding-bottom: 8px; }
        .hdr td { vertical-align: top; }
        .logo { max-height: 48px; }
        .lab { font-size: 9pt; color: #333; }
        .right { text-align: right; }
        .num { font-size: 14pt; font-weight: bold; }
        .meta { font-size: 10pt; margin-top: 4px; }
        .checks { margin: 10px 0; font-size: 9pt; }
        .checks span { margin-right: 16px; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.grid th, table.grid td { border: 1px solid #222; padding: 5px 6px; }
        table.grid th { background: #eee; font-size: 8pt; text-transform: uppercase; }
        .td-right { text-align: right; }
        .total { margin-top: 12px; text-align: right; font-size: 11pt; font-weight: bold; }
        .block { margin-top: 14px; font-size: 9pt; }
        .block h3 { font-size: 9pt; margin: 0 0 4px 0; text-transform: uppercase; border-bottom: 1px solid #999; padding-bottom: 2px; }
        .footer { margin-top: 18px; font-size: 7.5pt; color: #444; border-top: 1px solid #ccc; padding-top: 6px; }
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
                <div class="lab" style="margin-top:2px;font-weight:bold;">DEVIS INVISALIGN</div>
                @if(!empty($labAdresse))
                <div class="lab">{!! nl2br(e($labAdresse)) !!}</div>
                @endif
                @if(!empty($labPhone) || !empty($labEmail))
                <div class="lab">
                    @if(!empty($labPhone))Tél. {{ $labPhone }}@endif
                    @if(!empty($labPhone) && !empty($labEmail)) — @endif
                    @if(!empty($labEmail)){{ $labEmail }}@endif
                </div>
                @endif
            </td>
            <td class="right" style="width:45%">
                <div class="num">DEVIS N° {{ $devis->numero }}</div>
                <div class="meta">Date : {{ $devis->date_devis?->format('d/m/Y') }}</div>
                <div class="meta"><strong>Client :</strong> {{ $devis->client_nom }}</div>
                <div class="meta"><strong>Patient :</strong> {{ $devis->patient_nom }}</div>
            </td>
        </tr>
    </table>

    <div class="checks">
        <span><strong>MAXILLAIRE</strong> : {{ $devis->opt_maxillaire ? 'OUI' : 'NON' }}</span>
        <span><strong>MANDIBULAIRE</strong> : {{ $devis->opt_mandibulaire ? 'OUI' : 'NON' }}</span>
        <span><strong>TAQUETS</strong> : {{ $devis->taquets ? 'OUI' : 'NON' }}</span>
        <span><strong>STRIPPING</strong> : {{ $devis->stripping ? 'OUI' : 'NON' }}</span>
    </div>

    <table class="grid">
        <thead>
            <tr>
                <th style="width:46%">Désignation</th>
                <th style="width:18%">Arcade</th>
                <th style="width:18%">Prix unitaire ({{ $devise }})</th>
                <th style="width:18%">Total TTC ({{ $devise }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devis->lignes as $l)
            <tr>
                <td>{{ $l->designation }}</td>
                <td>{{ $l->arcade ?: '—' }}</td>
                <td class="td-right">{{ number_format((float) $l->prix_unitaire, 2, ',', ' ') }}</td>
                <td class="td-right">{{ number_format((float) $l->total_ttc, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">TOTAL TTC : {{ number_format((float) $devis->total_ttc, 2, ',', ' ') }} {{ $devise }}</div>

    @if($devis->conditions_paiement)
    <div class="block">
        <h3>Conditions de paiement</h3>
        <div>{!! nl2br(e($devis->conditions_paiement)) !!}</div>
    </div>
    @endif

    @if($devis->banque_ligne || $devis->beneficiaire || $devis->rib || $devis->commandes_email)
    <div class="block">
        <h3>Coordonnées bancaires &amp; commandes</h3>
        @if($devis->banque_ligne)<div>{{ $devis->banque_ligne }}</div>@endif
        @if($devis->beneficiaire)<div><strong>Bénéficiaire :</strong> {{ $devis->beneficiaire }}</div>@endif
        @if($devis->rib)<div><strong>RIB :</strong> {{ $devis->rib }}</div>@endif
        @if($devis->commandes_email)<div><strong>E-mail commandes :</strong> {{ $devis->commandes_email }}</div>@endif
    </div>
    @endif

    @if(!empty($footerSociete))
    <div class="footer">{!! nl2br(e($footerSociete)) !!}</div>
    @endif
</body>
</html>
