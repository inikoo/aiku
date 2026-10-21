<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\PalletReturn;

use App\Enums\EnumHelperTrait;

enum PalletReturnAttachmentScopeEnum: string
{
    use EnumHelperTrait;

    case INVOICE        = 'Invoice';
    case DELIVERY_NOTE  = 'Delivery note';
    case SHIPPING_LABEL = 'Shipping label';
    case OTHER          = 'Other';

    public static function labels(): array
    {
        return [
            'Invoice'        => __('Invoice'),
            'Delivery note'  => __('Delivery note'),
            'Shipping label' => __('Shipping label'),
            'Other'          => __('Other'),
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

    /**
     * @return array<int, string>
     */
    public static function parcelDocumentScopes(): array
    {
        return [
            self::INVOICE->value,
            self::DELIVERY_NOTE->value,
        ];
    }

    public function isPdfOnly(): bool
    {
        return $this !== self::OTHER;
    }
}
