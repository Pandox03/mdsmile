@php
    $typeLabel = \App\Support\TypePaiement::label($facture->type_paiement);
@endphp
@if($typeLabel !== '—')
<div style="font-size: 11px; margin: -6px 0 14px 0;">Type de paiement : {{ $typeLabel }}</div>
@endif
