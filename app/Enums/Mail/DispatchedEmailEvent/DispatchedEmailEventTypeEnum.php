<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 22:09:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\DispatchedEmailEvent;

use App\Enums\EnumHelperTrait;

enum DispatchedEmailEventTypeEnum: string
{
    use EnumHelperTrait;

    case DELIVERY = 'delivery';
    case BOUNCE   = 'bounce';

    case COMPLAIN       = 'complain';
    case OPEN           = 'open';
    case CLICK          = 'click';
    case DELIVERY_DELAY = 'delivery-delay';

    case UNSUBSCRIBE          = 'unsubscribe';

}
