<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:44:31 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\CRM\Prospect;

use App\Enums\EnumHelperTrait;

enum ProspectStateEnum: string
{
    use EnumHelperTrait;

    case NO_CONTACTED   = 'no-contacted';
    case CONTACTED      = 'contacted';
    case NOT_INTERESTED = 'not-interested';
    case REGISTERED     = 'registered';
    case INVOICED       = 'invoiced';
    case BOUNCED        = 'bounced';
}
