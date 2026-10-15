<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\ProcurementNote;

use App\Enums\EnumHelperTrait;

enum ProcurementNoteIncidentOutcomeEnum: string
{
    use EnumHelperTrait;

    case OPEN = 'open';
    case REJECTED_BY_US = 'rejected_by_us';
    case CREDITED = 'credited';
    case ACCEPTED = 'accepted';
    case WRITTEN_OFF = 'written_off';

    public static function labels(): array
    {
        return [
            'open'           => __('Open'),
            'rejected_by_us' => __('Rejected by us'),
            'credited'       => __('Credited'),
            'accepted'       => __('Accepted'),
            'written_off'    => __('Written off'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
