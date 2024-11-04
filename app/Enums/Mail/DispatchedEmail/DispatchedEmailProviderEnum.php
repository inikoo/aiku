<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 03 Nov 2024 13:25:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\DispatchedEmail;

use App\Enums\EnumHelperTrait;

enum DispatchedEmailProviderEnum: string
{
    use EnumHelperTrait;

    case SES    = 'ses';
    case MAILGUN = 'mailgun';
    case RESEND = 'resend';
    case MAILERSEND = 'mailersend';
    case ALIBABA    = 'alibaba';
    case SENDMAIL = 'sendmail';

}
