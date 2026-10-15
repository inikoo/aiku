<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\ProcurementNote;

use App\Enums\EnumHelperTrait;

enum ProcurementNoteStateEnum: string
{
    use EnumHelperTrait;

    case ACTIVE = 'active';
    case RESOLVED = 'resolved';
    case SUPERSEDED = 'superseded';

    public static function labels(): array
    {
        return [
            'active'     => __('Active'),
            'resolved'   => __('Resolved'),
            'superseded' => __('Superseded'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
