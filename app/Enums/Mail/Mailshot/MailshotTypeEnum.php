<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 22:03:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\Mailshot;

use App\Enums\EnumHelperTrait;

enum MailshotTypeEnum: string
{
    use EnumHelperTrait;

    case PROSPECT_MAILSHOT          = 'prospect_mailshot';
    case NEWSLETTER                 = 'newsletter';
    case CUSTOMER_PROSPECT_MAILSHOT = 'customer_prospect_mailshot';
    case MARKETING                  = 'marketing';
    case ANNOUNCEMENT               = 'announcement';


}
