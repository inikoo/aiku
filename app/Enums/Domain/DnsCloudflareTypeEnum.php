<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 28 Jun 2023 10:27:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Domain;

use App\Enums\EnumHelperTrait;

enum DnsCloudflareTypeEnum: string
{
    use EnumHelperTrait;

    case MX = 'MX';
    case CNAME = 'CNAME';
    case A = 'A';
    case TXT = 'TXT';
}
