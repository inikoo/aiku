<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 07 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Enums\Web\Seo;

use App\Enums\EnumHelperTrait;

enum SeoKeywordFrequencyEnum: string
{
    use EnumHelperTrait;

    case ON_REQUEST = 'on_request';
    case MONTHLY    = 'monthly';
    case WEEKLY     = 'weekly';
    case DAILY      = 'daily';

    public static function labels(): array
    {
        return [
            self::ON_REQUEST->value => __('On request'),
            self::MONTHLY->value    => __('Monthly'),
            self::WEEKLY->value     => __('Weekly'),
            self::DAILY->value      => __('Daily'),
        ];
    }

    /**
     * @return array<int, self>
     */
    public static function scheduled(): array
    {
        return [self::MONTHLY, self::WEEKLY, self::DAILY];
    }

    /**
     * Days after a check before the next one is due; null when it is only checked on request.
     */
    public function daysBetweenChecks(): ?int
    {
        return match ($this) {
            self::ON_REQUEST => null,
            self::MONTHLY    => 30,
            self::WEEKLY     => 7,
            self::DAILY      => 1,
        };
    }
}
