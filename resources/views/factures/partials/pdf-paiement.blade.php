@php
    $statut = $facture->statut_paiement ?? \App\Models\Facture::STATUT_NON_PAYEE;
    $statutLabel = \App\Models\Facture::statutPaiementLabels()[$statut] ?? 'Non payée';
    $typeLabel = \App\Support\TypePaiement::label($facture->type_paiement);
    $showType = $statut === \App\Models\Facture::STATUT_PAYEE && $typeLabel !== '—';
@endphp
<div style="font-size: 11px; margin: -6px 0 14px 0;">Paiement : {{ $statutLabel }}@if($showType) — Type : {{ $typeLabel }}@endif</div>
