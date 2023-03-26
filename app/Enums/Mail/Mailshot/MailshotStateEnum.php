<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\Mailshot;

use App\Enums\EnumHelperTrait;

//enum('InProcess','SetRecipients','ComposingEmail','Ready','Scheduled','Sending','Sent','Cancelled','Stopped')
enum MailshotStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS      = 'in-process';
    case SET_RECIPIENTS  = 'set-recipients';
    case COMPOSING_EMAIL = 'composing-email';
    case READY           = 'ready';
    case SCHEDULED       = 'scheduled';
    case SENDING         = 'sending';
    case SENT            = 'sent';
    case CANCELLED       = 'cancelled';
    case STOPPED         = 'stopped';
}
