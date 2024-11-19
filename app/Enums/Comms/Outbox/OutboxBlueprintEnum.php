<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\Outbox;

use App\Enums\EnumHelperTrait;

enum OutboxBlueprintEnum: string
{
    use EnumHelperTrait;
    case EMAIL_TEMPLATE = 'email_template';
    case MAILSHOT       = 'mailshot';
    case INVITE       = 'invite';
    case TEST           = 'test';
}
