<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Apr 2023 17:11:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrder;

use App\Enums\EnumHelperTrait;

//        //enum('Cancelled','NoReceived','InProcess',
//'Submitted','Confirmed','Manufactured','QC_Pass','Inputted','Dispatched','Received','Checked','Placed','Costing','InvoiceChecked')

enum PurchaseOrderStateEnum: string
{
    use EnumHelperTrait;

    case CREATING     = 'creating';
    case SUBMITTED    = 'submitted';
    case CONFIRMED    = 'confirmed';
    case MANUFACTURED ='manufactured';
    case DISPATCHED   = 'dispatched';
    case RECEIVED     = 'received';
    case CHECKED      = 'checked';
    case  SETTLED     = 'settled';


}
