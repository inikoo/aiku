<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 13:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Helpers\Snapshot;

use App\Enums\EnumHelperTrait;

enum SnapshotBuilderEnum: string
{
    use EnumHelperTrait;

    case UNLAYER = 'unlayer';
    case BEEFREE = 'beefree';
    case REPO_SOURCE_CODE = 'repo-source-code';
    case AIKU_WEB_BLOCKS_V1 = 'aiku-web-blocks-v1';


    public static function labels(): array
    {
        return [
            'unlayer'          => __('Unlayer'),
            'beefree'          => __('BeeFree'),
            'repo-source-code' => __('Hard Coded')
        ];
    }


}