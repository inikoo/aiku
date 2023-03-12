<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Mailroom\Outbox;

use App\Enums\EnumHelperTrait;
use Illuminate\Support\Str;

enum OutboxScopeEnum: string
{
    use EnumHelperTrait;

    case MARKETING             = 'marketing';
    case CUSTOMER_NOTIFICATION = 'customer_notification';
    case USER_NOTIFICATION     = 'user_notification';

    public function label(): string
    {
        return match ($this) {
            OutboxScopeEnum::MARKETING             => 'Marketing',
            OutboxScopeEnum::CUSTOMER_NOTIFICATION => 'Customer notifications',
            OutboxScopeEnum::USER_NOTIFICATION     => 'User notifications',
        };
    }


    public static function scopedLabels(): array
    {
        return collect(OutboxScopeEnum::cases())->mapWithKeys(function ($case) {
            return [Str::kebab($case->value) => $case->label()];
        })->all();
    }
}
