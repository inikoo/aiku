<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\EmailTrackingEvent;

use App\Enums\EnumHelperTrait;

enum EmailTrackingEventTypeEnum: string
{
    use EnumHelperTrait;

    case SENT                 = 'sent';
    case DECLINED_BY_PROVIDER = 'declined-by-provider';
    case DELIVERED            = 'delivered';
    case OPENED               = 'opened';
    case CLICKED              = 'clicked';
    case SOFT_BOUNCE          = 'soft_bounce';
    case HARD_BOUNCE          = 'hard_bounce';
    case MARKED_AS_SPAM       = 'marked-as-spam';
    case ERROR                = 'error';
}
