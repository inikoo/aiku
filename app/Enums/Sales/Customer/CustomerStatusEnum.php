<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Mar 2023 12:56:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Sales\Customer;

use App\Enums\EnumHelperTrait;

enum CustomerStatusEnum: string
{
    use EnumHelperTrait;

    case PENDING_APPROVAL = 'pending-approval';
    case APPROVED         = 'approved';
    case REJECTED         = 'rejected';
    case BANNED           = 'banned';
}
