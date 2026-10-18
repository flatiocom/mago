<?php

declare(strict_types=1);

final class Billing
{
    public bool $company = false;
    public null|string $vat = null;
}

function vat_label(Billing $billing): string
{
    if ($billing->company && strlen($billing->vat ?? '') > 0) {
        return ', VAT: ' . $billing->vat;
    }

    return '' . $billing->vat; // @mago-expect analysis:possibly-null-operand
}

function vat_or_nothing(Billing $billing): string
{
    if (strlen($billing->vat ?? '') < 1) {
        return '' . $billing->vat; // @mago-expect analysis:possibly-null-operand
    }

    return ', VAT: ' . $billing->vat;
}

function a_long_fallback_keeps_null(Billing $billing): string
{
    if (strlen($billing->vat ?? 'none') > 0) {
        return ', VAT: ' . $billing->vat; // @mago-expect analysis:possibly-null-operand
    }

    return '';
}
