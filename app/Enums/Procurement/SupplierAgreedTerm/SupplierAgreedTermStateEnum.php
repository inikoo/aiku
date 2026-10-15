<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\SupplierAgreedTerm;

use App\Enums\EnumHelperTrait;

enum SupplierAgreedTermStateEnum: string
{
    use EnumHelperTrait;

    case ACTIVE = 'active';
    case SUPERSEDED = 'superseded';
    case WITHDRAWN = 'withdrawn';

    public static function labels(): array
    {
        return [
            'active'     => __('Active'),
            'superseded' => __('Superseded'),
            'withdrawn'  => __('Withdrawn'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
