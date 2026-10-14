<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\ProcurementNote;

use App\Enums\EnumHelperTrait;

enum ProcurementNoteVisibilityEnum: string
{
    use EnumHelperTrait;

    case INTERNAL = 'internal';
    case AGENT = 'agent';
    case SUPPLIER = 'supplier';

    public static function labels(): array
    {
        return [
            'internal' => __('Internal'),
            'agent'    => __('Agent'),
            'supplier' => __('Supplier'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
