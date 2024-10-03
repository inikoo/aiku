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
use App\Events\BroadcastPreviewWebpage;
use App\Http\Resources\Web\WebpageResource;
use App\Models\Dropshipping\ModelHasWebBlocks;
use Lorisleiva\Actions\ActionRequest;

class DeleteModelHasWebBlocks extends GrpAction
{
    use HasWebAuthorisation;


    public function handle(ModelHasWebBlocks $modelHasWebBlocks): ModelHasWebBlocks
    {
        $webBlockUsed = ModelHasWebBlocks::where('web_block_id', $modelHasWebBlocks->web_block_id)->count();

        if ($webBlockUsed === 1) {
            $modelHasWebBlocks->webBlock()->delete();
        }

        $modelHasWebBlocks->delete();

        UpdateWebpageContent::run($modelHasWebBlocks->webpage);

        BroadcastPreviewWebpage::dispatch($modelHasWebBlocks->webpage);

        return $modelHasWebBlocks;
    }

    public function jsonResponse(ModelHasWebBlocks $modelHasWebBlocks): void
    {
        WebpageResource::make($modelHasWebBlocks->webpage);
    }

    public function asController(ModelHasWebBlocks $modelHasWebBlocks, ActionRequest $request): void
    {
        $this->initialisation($modelHasWebBlocks->group, $request);

        $this->handle($modelHasWebBlocks);
    }

    public function action(ModelHasWebBlocks $modelHasWebBlocks, array $modelData): ModelHasWebBlocks
    {
        $this->asAction = true;

        $this->initialisation($modelHasWebBlocks->group, $modelData);

        return $this->handle($modelHasWebBlocks);
    }
}
