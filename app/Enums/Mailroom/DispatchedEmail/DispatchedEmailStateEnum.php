<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Mailroom\DispatchedEmail;

use App\Enums\EnumHelperTrait;

//enum('Ready','Sent to SES','Rejected by SES','Sent','Soft Bounce','Hard Bounce','Delivered','Spam','Opened','Clicked','Error')
enum DispatchedEmailStateEnum: string
{
    use EnumHelperTrait;

    case READY                = 'ready';
    case SENT_TO_PROVIDER     = 'sent-to-provider';
    case REJECTED_BY_PROVIDER = 'rejected-by-provider';
    case SENT                 = 'sent';
    case OPENED               = 'opened';
    case CLICKED              = 'clicked';
    case SOFT_BOUNCE          = 'soft-bounce';
    case HARD_BOUNCE          = 'hard-bounce';
    case DELIVERED            = 'delivered';
    case MARKED_AS_SPAM       = 'marked-as-spam';

    case ERROR = 'error';
}
