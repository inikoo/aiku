<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\SupplierAgreedTerm;

use App\Enums\EnumHelperTrait;

enum SupplierAgreedTermValueTypeEnum: string
{
    use EnumHelperTrait;

    case INCLUDED = 'included';
    case NOT_ALLOWED = 'not_allowed';
    case FIXED_AMOUNT = 'fixed_amount';
    case PER_UNIT = 'per_unit';
    case PER_CARTON = 'per_carton';
    case PER_ORDER = 'per_order';
    case PERCENTAGE = 'percentage';
    case TEXT = 'text';

    public static function labels(): array
    {
        return [
            'included'     => __('Included'),
            'not_allowed'  => __('Not allowed'),
            'fixed_amount' => __('Fixed amount'),
            'per_unit'     => __('Per unit'),
            'per_carton'   => __('Per carton'),
            'per_order'    => __('Per order'),
            'percentage'   => __('Percentage'),
            'text'         => __('Text'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function hasAmount(): bool
    {
        return !in_array($this, [self::INCLUDED, self::NOT_ALLOWED, self::TEXT]);
    }
}
