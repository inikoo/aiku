<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jun 2024 20:54:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlock;

use App\Actions\GrpAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Models\Web\WebBlock;
use App\Models\Web\WebBlockType;

class UpdateWebBlock extends GrpAction
{
    use HasWebAuthorisation;


    public function handle(WebBlock $webBlock, array $modelData): WebBlock
    {
        data_set($modelData, 'layout', $webBlock->toArray());

        /** @var WebBlock $webBlock */
        $webBlock = $webBlock->webBlocks()->create($modelData);
        return $webBlock;
    }



    public function action(WebBlockType $webBlockType, array $modelData): WebBlock
    {
        $this->asAction = true;

        $this->initialisation($webBlockType->group, $modelData);

        return $this->handle($webBlockType, $this->validatedData);
    }

}
