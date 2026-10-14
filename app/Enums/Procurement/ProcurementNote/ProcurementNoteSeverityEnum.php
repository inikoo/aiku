<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\ProcurementNote;

use App\Enums\EnumHelperTrait;

enum ProcurementNoteSeverityEnum: string
{
    use EnumHelperTrait;

    case INFO = 'info';
    case WARNING = 'warning';
    case CRITICAL = 'critical';

    public static function labels(): array
    {
        return [
            'info'     => __('Info'),
            'warning'  => __('Warning'),
            'critical' => __('Critical'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
