<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 22:10:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\MailshotSendChannel;

use App\Enums\EnumHelperTrait;

enum MailshotSendChannelStateEnum: string
{
    use EnumHelperTrait;

    case READY      = 'ready';
    case SENDING    = 'sending';
    case SENT       = 'sent';
    case STOPPED    = 'stopped';

}
