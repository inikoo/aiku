<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 02:05:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Leads\Prospect;

use App\Enums\EnumHelperTrait;

//enum('NoContacted','Contacted','NotInterested','Registered','Invoiced','Bounced')
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
