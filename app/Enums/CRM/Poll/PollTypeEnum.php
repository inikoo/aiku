<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 17 Nov 2024 15:02:50 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\CRM\Poll;

use App\Enums\EnumHelperTrait;

enum PollTypeEnum: string
{
    use EnumHelperTrait;

    case OPEN_QUESTION     = 'open_question';
    case OPTION            = 'option';

}
