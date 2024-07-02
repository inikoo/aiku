<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jul 2024 14:31:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\EmailTemplate;

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
