<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 22 Nov 2024 12:15:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\Outbox;

use App\Enums\EnumHelperTrait;

enum OutboxTypeEnum: string
{
    use EnumHelperTrait;

    case NEWSLETTER = 'newsletter';
    case MARKETING  = 'marketing';
    case NOTIFICATION = 'notification'; // halfway between marketing and transactional
    case TRANSACTIONAL = 'transactional';
    case COLD_EMAIL = 'cold-email';
    case APP_COMMS = 'app-comms';



}
