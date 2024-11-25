<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 13:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\Email;

use App\Enums\EnumHelperTrait;

enum EmailBuilderEnum: string
{
    use EnumHelperTrait;

    case UNLAYER = 'unlayer';
    case BEEFREE = 'beefree';
    case REPO_SOURCE_CODE = 'repo-source-code';


    public static function labels(): array
    {
        return [
            'unlayer'          => __('Unlayer'),
            'beefree'          => __('BeeFree'),
            'repo-source-code' => __('Hard Coded')
        ];
    }


}
