<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:12:19 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlock;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Web\WebBlock;

class UpdateWebBlock
{
    use WithActionUpdate;


    public function handle(WebBlock $webBlock, array $modelData): WebBlock
    {
        return $this->update($webBlock, $modelData, ['data']);
    }


}
