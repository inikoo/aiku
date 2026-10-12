<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Enums\Web\Seo;

use App\Enums\EnumHelperTrait;

enum SeoContentBriefStateEnum: string
{
    use EnumHelperTrait;

    case PENDING = 'pending';
    case READY   = 'ready';
    case FAILED  = 'failed';
}
