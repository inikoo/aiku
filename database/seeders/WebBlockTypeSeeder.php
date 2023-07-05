<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 14:44:14 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Seeders;

use App\Actions\Web\WebBlockType\StoreWebBlockType;
use App\Actions\Web\WebBlockType\UpdateWebBlockType;
use App\Models\Web\WebBlockType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class WebBlockTypeSeeder extends Seeder
{
    public function run(): void
    {
        $webBlockTypes = json_decode(Storage::disk('datasets')->get('web-block-types.json'), true);

        foreach ($webBlockTypes as $webBlockTypeData) {

            $webBlockType=WebBlockType::where('code', $webBlockTypeData['code'])->first();
            if($webBlockType) {
                UpdateWebBlockType::run($webBlockType, $webBlockTypeData);
            } else {
                StoreWebBlockType::run($webBlockTypeData);
            }

        }
    }
}
