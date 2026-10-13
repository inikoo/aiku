<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 09:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrder;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderPaymentMethodEnum: string
{
    use EnumHelperTrait;

    case BANK_TRANSFER = 'bank_transfer';
    case CARD = 'card';
    case PAYPAL = 'paypal';
    case CASH = 'cash';
    case OTHER = 'other';

    public static function labels(): array
    {
        return [
            'bank_transfer' => __('Bank transfer'),
            'card'          => __('Card'),
            'paypal'        => __('PayPal'),
            'cash'          => __('Cash'),
            'other'         => __('Other'),
        ];
    }

    /**
     * @return array<int, array{name: string, code: string}>
     */
    public static function options(): array
    {
        return collect(self::labels())
            ->map(fn (string $label, string $code) => ['name' => $label, 'code' => $code])
            ->values()
            ->all();
    }
}
