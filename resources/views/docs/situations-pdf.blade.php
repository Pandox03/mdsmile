<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Situation {{ $doc->name }} - {{ $monthLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; line-height: 1.4; padding: 18mm; background: #fff; }
        .header { display: table; width: 100%; margin-bottom: 16px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .logo img { max-height: 70px; max-width: 160px; display: block; }
        .title-block { text-align: center; background: #d4a574; color: #000; padding: 12px 16px; margin-bottom: 12px; font-size: 16px; font-weight: bold; }
        .date-line { margin-bottom: 14px; font-size: 11px; color: #333; }
        .doc-block { background: #d4a574; color: #000; padding: 10px 16px; margin-bottom: 16px; font-size: 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 10px; font-weight: bold; padding: 10px 12px; text-align: left; border: 1px solid #000; background: #d4a574; color: #000; }
        th.amount { text-align: right; }
        td { padding: 8px 12px; border: 1px solid #333; font-size: 11px; }
        td.amount { text-align: right; }
        .total-row { font-weight: bold; background: #f0f0f0; }
        .total-row td { border: 1px solid #000; padding: 10px 12px; }
        .total-row .total-cell { background: #d4a574; color: #000; font-size: 12px; }
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
            <span style="font-size: 18px; font-weight: bold;">{{ $labName ?? 'MD Smile' }}</span>
            @endif
        </div>
        <div class="header-right">
            @if(isset($logoPath) && file_exists($logoPath))
            <div class="logo" style="margin-left: auto;">
                <img src="{{ $logoPath }}" alt="{{ $labName ?? 'Lab' }}" style="max-height: 50px;">
            </div>
            @endif
        </div>
    </div>

    <div class="title-block">LABORATOIRE DE PROTHÈSE DENTAIRE</div>

    <div class="date-line">{{ $labVille ?? 'Casablanca' }} — Situation du mois : {{ $monthLabel }}</div>

    <p style="margin-bottom: 12px; font-size: 10px; color: #333;">Montants de travaux du mois (Annulé / À refaire = 0 DHS). Encaissements : date = jour d’enregistrement. Situation du mois = report + travaux du mois.</p>

    <div class="doc-block">Dr {{ $doc->name }}</div>

    @if($carryover > 0)
    <table style="margin-bottom: 12px;">
        <tbody>
            <tr>
                <td style="font-weight: bold;">Reste du mois précédent</td>
                <td class="amount">{{ number_format($carryover, 0, ',', ' ') }} DHS</td>
            </tr>
        </tbody>
    </table>
    @endif

    <table>
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
            @php $lineCount = count($group['lines']); @endphp
            @foreach($group['lines'] as $i => $line)
            <tr>
                @if($i === 0)
                <td rowspan="{{ $lineCount }}" style="vertical-align: top;">{{ $group['patient'] }}</td>
                <td rowspan="{{ $lineCount }}" style="vertical-align: top;">{{ $group['numero_fiche'] }}</td>
                @endif
                <td>{{ $line['nature'] }}</td>
                @if($i === 0)
                <td rowspan="{{ $lineCount }}" class="amount" style="vertical-align: top;">{{ number_format((float) $group['amount'], 0, ',', ' ') }}</td>
                @endif
            </tr>
            @endforeach
            @endforeach
        </tbody>
        @if(count($groups) > 0 || $carryover > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="3">SITUATION DU MOIS</td>
                <td class="amount total-cell">{{ number_format($situationTotal, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

</body>
</html>
