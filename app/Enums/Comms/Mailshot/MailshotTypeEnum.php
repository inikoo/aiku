<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\Mailshot;

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
