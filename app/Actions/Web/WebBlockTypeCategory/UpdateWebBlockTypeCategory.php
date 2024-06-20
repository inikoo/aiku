<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:12:19 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlockTypeCategory;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Web\WebBlockTypeCategory;

class UpdateWebBlockTypeCategory
{
    use WithActionUpdate;


    public function handle(WebBlockTypeCategory $webBlockType, array $modelData): WebBlockTypeCategory
    {
        return $this->update($webBlockType, $modelData, ['data']);
    }


}
