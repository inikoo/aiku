<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jul 2024 13:51:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\Outbox;

use App\Enums\EnumHelperTrait;

enum OutboxBlueprintEnum: string
{
    use EnumHelperTrait;
    case EMAIL_TEMPLATE = 'email_template';
    case MAILSHOT       = 'mailshot';
}
