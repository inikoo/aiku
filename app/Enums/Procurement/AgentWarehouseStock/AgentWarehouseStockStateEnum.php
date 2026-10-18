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

    case RECEIVED = 'received';
    case PACKED_FOR_NEXT_CONTAINER = 'packed_for_next_container';
    case PACKED_FOR_LATER_CONTAINER = 'packed_for_later_container';

    public static function labels(): array
    {
        return [
            'received'                => __('Received, not packed yet'),
            'packed_for_next_container'  => __('Packed, next container'),
            'packed_for_later_container' => __('Packed, container after next'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function icon(): array
    {
        return match ($this) {
            self::RECEIVED => ['tooltip' => $this->label(), 'icon' => 'fal fa-dolly', 'class' => 'text-amber-500'],
            self::PACKED_FOR_NEXT_CONTAINER => ['tooltip' => $this->label(), 'icon' => 'fal fa-box-check', 'class' => 'text-green-600'],
            self::PACKED_FOR_LATER_CONTAINER => ['tooltip' => $this->label(), 'icon' => 'fal fa-boxes', 'class' => 'text-gray-500'],
        };
    }
}
