<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 09 Oct 2024 09:53:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlock;

use App\Actions\GrpAction;
use App\Models\Web\WebBlock;
use Illuminate\Support\Facades\DB;

class DeleteWebBlock extends GrpAction
{
    public function handle(WebBlock $webBlock): void
    {

        DB::table("model_has_web_blocks")
            ->where("web_block_id", $webBlock->id)
            ->delete();

        DB::table("model_has_media")
            ->where("model_type", 'WebBlock')
            ->where("model_id", $webBlock->id)
            ->delete();


        $webBlock->forceDelete();

    }



}
