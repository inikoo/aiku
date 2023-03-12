<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 19:27:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Mailroom\EmailTrackingEvent;

use App\Enums\EnumHelperTrait;

enum EmailTrackingEventTypeEnum: string
{
    use EnumHelperTrait;

    case SENT                 = 'sent';
    case DECLINED_BY_PROVIDER = 'declined-by-provider';
    case DELIVERED            = 'delivered';
    case OPENED               = 'opened';
    case CLICKED              = 'clicked';
    case SOFT_BOUNCE          = 'soft-bounce';
    case HARD_BOUNCE          = 'hard-bounce';
    case MARKED_AS_SPAM       = 'marked-as-spam';
    case ERROR                = 'error';
}
