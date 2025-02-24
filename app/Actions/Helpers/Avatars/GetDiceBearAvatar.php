<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 09 Dec 2023 03:25:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Avatars;

use App\Enums\Helpers\Avatars\DiceBearStylesEnum;
use Exception;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDiceBearAvatar
{
    use AsAction;

    public function handle(DiceBearStylesEnum $style, string $seed): string
    {


        if (config('app.dice_bear.mock')) {
            return Storage::disk('art')->get('icons/'.$style->value.'.svg');
        }

        try {
            $svg = file_get_contents(config('app.dice_bear.url') ."/".$style->value."/svg?seed=$seed");
        } catch (Exception) {
            return Storage::disk('art')->get('icons/'.$style->value.'.svg');
        }
        return $svg;


    }

}
