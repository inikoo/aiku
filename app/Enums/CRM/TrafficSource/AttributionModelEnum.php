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
    case U_SHAPED = 'u_shaped';
    case W_SHAPED = 'w_shaped';

    public static function labels(): array
    {
        return [
            self::LINEAR->value      => __('Linear'),
            self::FIRST_TOUCH->value => __('First touch'),
            self::U_SHAPED->value    => __('U-shaped'),
            self::W_SHAPED->value    => __('W-shaped'),
        ];
    }

    public static function descriptions(): array
    {
        return [
            self::LINEAR->value      => __('Split evenly between every channel and campaign that touched the customer within the attribution window before the order.'),
            self::FIRST_TOUCH->value => __('All of the order goes to one touch: for a new customer the earliest touch in the 90 days before they registered, claiming orders for 365 days; for anyone else their first recorded touch, claiming orders for 90 days.'),
            self::U_SHAPED->value    => __('40% to the first touch, 40% to the last touch before the order, and 20% split between the touches in between. A touch that plays both parts takes them both, and the parts that are missing leave the rest scaled up to 100%.'),
            self::W_SHAPED->value    => __('30% to the first touch, 30% to the last touch before registering, 30% to the last touch before the order, and 10% split between the touches in between. A touch that plays more than one part adds them up, and the parts that are missing leave the rest scaled up to 100%.'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
