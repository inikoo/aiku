<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Jun 2024 13:18:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\ModelHasWebBlocks;

use App\Actions\GrpAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Http\Resources\Web\WebpageResource;
use App\Models\ModelHasWebBlocks;
use Illuminate\Http\Resources\Json\JsonResource;

class DeleteModelHasWebBlocks extends GrpAction
{
    use HasWebAuthorisation;


    public function handle(ModelHasWebBlocks $modelHasWebBlock): ModelHasWebBlocks
    {
        $webBlockUsed = ModelHasWebBlocks::where('web_block_id', $modelHasWebBlock->web_block_id)->count();

        if ($webBlockUsed === 1) {
            $modelHasWebBlock->webBlock()->delete();
        }

        $modelHasWebBlock->delete();

        UpdateWebpageContent::run($modelHasWebBlock->webpage);

        return $modelHasWebBlock;
    }

    public function jsonResponse(ModelHasWebBlocks $modelHasWebBlock): JsonResource
    {
        return WebpageResource::make($modelHasWebBlock->webpage);
    }

    public function action(ModelHasWebBlocks $modelHasWebBlocks, array $modelData): ModelHasWebBlocks
    {
        $this->asAction = true;

        $this->initialisation($modelHasWebBlocks->group, $modelData);

        return $this->handle($modelHasWebBlocks);
    }
}
