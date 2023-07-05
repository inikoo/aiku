<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 14:44:14 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Seeders;

use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Actions\Web\WebBlock\UpdateWebBlock;
use App\Actions\Web\WebBlockType\StoreWebBlockType;
use App\Actions\Web\WebBlockType\UpdateWebBlockType;
use App\Models\Web\WebBlockType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class WebBlockSeeder extends Seeder
{
    public function run(): void
    {
        $webBlockTypes = json_decode(Storage::disk('datasets')->get('web-blocks.json'), true);

        foreach ($webBlockTypes as $webBlockTypeData) {
            $webBlockType = WebBlockType::where('code', $webBlockTypeData['code'])->first();
            if ($webBlockType) {
                UpdateWebBlockType::run($webBlockType, Arr::except($webBlockTypeData, 'webBlocks'));
            } else {
                $webBlockType=StoreWebBlockType::run(Arr::except($webBlockTypeData, 'webBlocks'));
            }

            foreach (Arr::get($webBlockTypeData, 'webBlocks', []) as $webBlockData) {

                $webBlock=$webBlockType->webBlock()->where('code', Arr::get($webBlockData, 'code'))->first();
                if($webBlock) {
                    UpdateWebBlock::run($webBlock, $webBlockData);
                } else {
                    StoreWebBlock::run($webBlockType, $webBlockData);

                }

            }


        }
    }
}
