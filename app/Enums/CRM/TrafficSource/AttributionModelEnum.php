<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Enums\CRM\TrafficSource;

use App\Enums\EnumHelperTrait;

enum AttributionModelEnum: string
{
    use EnumHelperTrait;

    case LINEAR = 'linear';
    case FIRST_TOUCH = 'first_touch';

    public static function labels(): array
    {
        return [
            self::LINEAR->value      => __('Linear'),
            self::FIRST_TOUCH->value => __('First touch'),
        ];
    }

    public static function descriptions(): array
    {
        return [
            self::LINEAR->value      => __('Split evenly between every channel and campaign that touched the customer within the attribution window before the order.'),
            self::FIRST_TOUCH->value => __('All of the order goes to one touch: for a new customer the earliest touch in the 90 days before they registered, claiming orders for 365 days; for anyone else their first recorded touch, claiming orders for 90 days.'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
