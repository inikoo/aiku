<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Market\RentalAgreement;

use App\Enums\EnumHelperTrait;

enum RentalAgreementStateEnum: string
{
    use EnumHelperTrait;

    case DRAFT              = 'draft';
    case ACTIVE             = 'active';
    case EXPIRED            = 'expired';
}
