<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $facture->numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
            padding: 18mm 18mm 40mm 18mm; /* extra bottom padding so content doesn't overlap the fixed footer */
            background: #fff;
        }
        .header { display: table; width: 100%; margin-bottom: 20px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .logo img { max-height: 80px; max-width: 180px; display: block; }
        .date { font-size: 11px; color: #000; }
        .client-block { text-align: center; margin: 22px 0 18px 0; }
        .client-name { font-size: 13px; font-weight: bold; color: #000; margin-bottom: 4px; }
        .client-ice { font-size: 10px; color: #333; }
        .facture-num { font-size: 12px; font-weight: bold; margin-bottom: 14px; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { font-size: 10px; font-weight: bold; color: #000; padding: 8px 10px; text-align: left; border: 1px solid #000; background: #f8f8f8; }
        th:nth-child(2) { text-align: right; }
        td { padding: 8px 10px; border: 1px solid #333; font-size: 11px; }
        td:nth-child(2) { text-align: right; }
        .total-line { font-weight: bold; }
        .total-line td { border: 1px solid #000; }
        .montant-lettres { text-align: center; margin-top: 22px; font-size: 11px; text-decoration: underline; padding: 0 20px; }
        .stamp-area { margin-top: 28px; text-align: center; font-size: 10px; color: #333; }
        .stamp-area .name { font-weight: bold; margin-bottom: 4px; }
        .footer {
            position: fixed;
            left: 18mm;
            right: 18mm;
            bottom: 12mm;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            font-size: 9px;
            color: #333;
        }
        .footer .siege { margin-bottom: 4px; }
        .footer .ids { font-size: 9px; }
    </style>
</head>
<body>
    @if(empty($splitMontants))
    <div class="header">
        <div class="header-left">
            <div class="logo">
                @if(isset($logoPath) && file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="{{ $labName ?? 'MD Smile' }}">
                @else
                <span style="font-size: 20px; font-weight: bold; color: #000;">{{ $labName ?? 'MD Smile' }}</span>
                @endif
            </div>
        </div>
        <div class="header-right">
            <div class="date">Date : {{ $facture->date_facture->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="client-block">
        <div class="client-name">{{ $facture->doc->name }}</div>
        @if($facture->doc->numero_registration)
        <div class="client-ice">ICE : {{ $facture->doc->numero_registration }}</div>
        @endif
    </div>

    <div class="facture-num">FACTURE N°{{ $facture->numero }}</div>

    <table>
        <thead>
            <tr>
                <th>Nature de travail</th>
                <th>Montant ({{ $devise ?? 'DHS' }})</th>
            </tr>
        </thead>
        <tbody>
            @php $ratioHT = ($totalTTC ?? 0) > 0 ? ($totalHT ?? 0) / ($totalTTC ?? 1) : 1; @endphp
            @if(!empty($lignesReste))
            @foreach($lignesReste as $ligne)
            <tr>
                <td>{{ $ligne['label'] }}</td>
                <td>{{ number_format(round((float)$ligne['montant'] * $ratioHT, 2), 2, ',', ' ') }}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td>Divers travaux de prothèse dentaire</td>
                <td>{{ number_format($totalHT ?? $facture->montant_comptabilise_affiche, 2, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="total-line">
                <td>PRIX TOTAL (HT)</td>
                <td>{{ number_format($totalHT ?? $facture->montant_comptabilise_affiche, 2, ',', ' ') }}</td>
            </tr>
            <tr class="total-line">
                <td>TVA ({{ number_format($tvaRate ?? 20, 0) }}%)</td>
                <td>{{ number_format($tvaAmount ?? 0, 2, ',', ' ') }}</td>
            </tr>
            <tr class="total-line">
                <td>TOTAL TTC</td>
                <td>{{ number_format($totalTTC ?? $facture->montant_comptabilise_affiche, 2, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="montant-lettres">
        Arrêtée la présente facture à la somme de {{ $montantEnLettres ?? '' }}.
    </div>

    @if($labName ?? $labAdresse ?? $labPhone ?? $labEmail ?? $labSiegeSocial ?? $labIce ?? $labTp ?? $labIf)
    <div class="footer">
        @if($labName ?? null)<div class="name">{{ $labName }}</div>@endif
      <!--
        write email: then email address 
        write phone: then phone number -->
        @if($labPhone ?? null )
        <div>
           
            @if($labPhone ?? null)Tel : {{ $labPhone }}@endif
           

        </div>
        @endif
        @if($labEmail ?? null)
        <div>
            @if($labEmail ?? null)Email : {{ $labEmail }}@endif
        </div>

        @endif
        @if($labSiegeSocial ?? null)<div class="siege">Siège social : {{ $labSiegeSocial }}</div>@endif
        @if($labIce ?? $labTp ?? $labIf)
        <div class="ids">
            @if($labIce ?? null)ICE : {{ $labIce }}@endif
            @if($labTp ?? null) TP : {{ $labTp }}@endif
            @if($labIf ?? null) IF : {{ $labIf }}@endif
        </div>
        @endif
    </div>
    @endif
    @else
        {{-- Split view: one page per part --}}
        @foreach($splitMontants as $i => $part)
        <div style="{{ $i > 0 ? 'page-break-before: always;' : '' }} padding-top: {{ $i > 0 ? '10mm' : '0' }};">
            <div class="header">
                <div class="header-left">
                    <div class="logo">
                        @if(isset($logoPath) && file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="{{ $labName ?? 'MD Smile' }}">
                        @else
                        <span style="font-size: 20px; font-weight: bold; color: #000;">{{ $labName ?? 'MD Smile' }}</span>
                        @endif
                    </div>
                </div>
                <div class="header-right"><div class="date">Date : {{ $facture->date_facture->format('d/m/Y') }}</div></div>
            </div>
            <div class="client-block">
                <div class="client-name">{{ $facture->doc->name }}</div>
                @if($facture->doc->numero_registration)<div class="client-ice">ICE : {{ $facture->doc->numero_registration }}</div>@endif
            </div>
            <div class="facture-num">FACTURE N°{{ $facture->numero }} — Partie {{ $i + 1 }}/{{ count($splitMontants) }}</div>
            @php
                $partTTC = $part;
                $partTVA = round($partTTC * ($tvaRate ?? 20) / 100, 2);
                $partHT = round($partTTC - $partTVA, 2);
                $partLettres = \App\Helpers\NumberToFrench::toLetters($partTTC, 'dirhams');
            @endphp
            <table>
                <thead><tr><th>Nature de travail</th><th>Montant ({{ $devise ?? 'DHS' }})</th></tr></thead>
                <tbody>
                    <tr><td>Facture {{ $facture->numero }} — Partie {{ $i + 1 }}</td><td>{{ number_format($partHT, 2, ',', ' ') }}</td></tr>
                    <tr class="total-line"><td>PRIX TOTAL (HT)</td><td>{{ number_format($partHT, 2, ',', ' ') }}</td></tr>
                    <tr class="total-line"><td>TVA ({{ number_format($tvaRate ?? 20, 0) }}%)</td><td>{{ number_format($partTVA, 2, ',', ' ') }}</td></tr>
                    <tr class="total-line"><td>TOTAL TTC</td><td>{{ number_format($partTTC, 2, ',', ' ') }}</td></tr>
                </tbody>
            </table>
            <div class="montant-lettres">Arrêtée la présente facture à la somme de {{ $partLettres }}.</div>
        </div>
        @endforeach
    @endif
</body>
</html>
