<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\AgentWarehouseStock;

use App\Enums\EnumHelperTrait;

enum AgentWarehouseStockStateEnum: string
{
    use EnumHelperTrait;

    case MOVING = 'moving';
    case PACKED = 'packed';
    case PACKED_NEXT_CONTAINER = 'packed_next_container';

    public static function labels(): array
    {
        return [
            'moving'                => __('Moving, waiting to be packed'),
            'packed'                => __('Packed'),
            'packed_next_container' => __('Packed, next container'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
