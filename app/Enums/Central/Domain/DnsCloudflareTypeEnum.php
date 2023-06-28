<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 28 Jun 2023 10:43:46 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Central\Domain;

use App\Enums\EnumHelperTrait;

enum DnsCloudflareTypeEnum: string
{
    use EnumHelperTrait;

    case MX    = 'MX';
    case CNAME = 'CNAME';
    case A     = 'A';
    case TXT   = 'TXT';
}
