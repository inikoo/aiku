<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:40:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Accounting\Invoice;

use App\Enums\EnumHelperTrait;

enum InvoiceTypeEnum: string
{
    use EnumHelperTrait;
    case INVOICE  = 'invoice';
    case REFUND   = 'refund';
}
