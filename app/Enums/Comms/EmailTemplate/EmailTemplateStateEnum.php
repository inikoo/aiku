<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\EmailTemplate;

use App\Enums\EnumHelperTrait;

enum EmailTemplateStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in-process';
    case LIVE       = 'live';


    public static function labels(): array
    {
        return [
            'in-process' => __('In construction'),
            'live'       => __('Live'),
        ];
    }



}
