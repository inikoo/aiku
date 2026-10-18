<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\ProcurementNote;

use App\Enums\EnumHelperTrait;

enum ProcurementNoteIncidentTypeEnum: string
{
    use EnumHelperTrait;

    case QUALITY = 'quality';
    case DAMAGED = 'damaged';
    case WRONG_ITEM = 'wrong_item';
    case SHORT_OVER = 'short_over';
    case PACKING_LABELS = 'packing_labels';
    case PRICE_CHANGED = 'price_changed';
    case LATE = 'late';

    public static function labels(): array
    {
        return [
            'quality'        => __('Quality'),
            'damaged'        => __('Damaged'),
            'wrong_item'     => __('Wrong item'),
            'short_over'     => __('Short or over'),
            'packing_labels' => __('Packing or labels'),
            'price_changed'  => __('Price changed'),
            'late'           => __('Late'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
