<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:30
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Enums\Procurement\AgentGoodsReceipt;

use App\Enums\EnumHelperTrait;

enum AgentGoodsReceiptStateEnum: string
{
    use EnumHelperTrait;

    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public static function labels(): array
    {
        return [
            'received'  => __('Received'),
            'cancelled' => __('Cancelled'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function icon(): array
    {
        return match ($this) {
            self::RECEIVED => ['tooltip' => $this->label(), 'icon' => 'fal fa-check-circle', 'class' => 'text-green-600'],
            self::CANCELLED => ['tooltip' => $this->label(), 'icon' => 'fal fa-times-circle', 'class' => 'text-red-500'],
        };
    }
}
