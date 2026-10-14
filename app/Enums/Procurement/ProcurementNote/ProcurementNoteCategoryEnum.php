<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\ProcurementNote;

use App\Enums\EnumHelperTrait;

enum ProcurementNoteCategoryEnum: string
{
    use EnumHelperTrait;

    case GENERAL = 'general';
    case AGENT = 'agent';
    case PRODUCT = 'product';
    case PACKAGING = 'packaging';
    case CHARGES = 'charges';
    case DELIVERY = 'delivery';
    case CURRENCY_PAYMENT = 'currency_payment';
    case INCIDENT = 'incident';

    public static function labels(): array
    {
        return [
            'general'          => __('General'),
            'agent'            => __('Agent'),
            'product'          => __('Product'),
            'packaging'        => __('Packaging'),
            'charges'          => __('Charges'),
            'delivery'         => __('Delivery'),
            'currency_payment' => __('Currency and payment'),
            'incident'         => __('Incident'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function colour(): string
    {
        return match ($this) {
            self::GENERAL          => 'gray',
            self::AGENT            => 'purple',
            self::PRODUCT          => 'blue',
            self::PACKAGING        => 'green',
            self::CHARGES          => 'red',
            self::DELIVERY         => 'orange',
            self::CURRENCY_PAYMENT => 'yellow',
            self::INCIDENT         => 'black',
        };
    }
}
