<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 28 Jun 2023 08:47:51 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Domain;

use App\Enums\EnumHelperTrait;

enum DomainCloudflareStatusEnum: string
{
    use EnumHelperTrait;

    case PENDING = 'pending';
    case ACTIVE  = 'active';
}
