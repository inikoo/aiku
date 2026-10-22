<?php

/*
 * Created: Thu, 22 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\ModelHasWebBlocks;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithWebEditAuthorisation;
use App\Actions\Web\WebBlockHistory\StoreWebBlockHistory;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Models\Dropshipping\ModelHasWebBlocks;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class ReplaceModelHasWebBlockContent extends OrgAction
{
    use WithWebEditAuthorisation;
    use WithPasteWebBlockFromSource;

    /**
     * @throws ValidationException
     */
    public function handle(ModelHasWebBlocks $modelHasWebBlocks, ModelHasWebBlocks $sourceModelHasWebBlocks): ModelHasWebBlocks
    {
        $webBlock       = $modelHasWebBlocks->webBlock;
        $sourceWebBlock = $sourceModelHasWebBlocks->webBlock;

        if ($webBlock->web_block_type_id !== $sourceWebBlock->web_block_type_id) {
            throw ValidationException::withMessages([
                'source' => __('Content and style can only be pasted into a block of the same type'),
            ]);
        }

        $layout = $this->layoutForWebsite($sourceWebBlock, $modelHasWebBlocks->website);

        $webBlock->update([
            'layout'   => $layout,
            'checksum' => md5(json_encode($layout)),
        ]);
        $this->copyImages($sourceWebBlock, $webBlock);

        StoreWebBlockHistory::make()->action($modelHasWebBlocks->refresh(), []);

        UpdateWebpageContent::run($modelHasWebBlocks->webpage);

        return $modelHasWebBlocks;
    }

    /**
     * @throws ValidationException
     */
    public function asController(ModelHasWebBlocks $modelHasWebBlocks, ModelHasWebBlocks $source, ActionRequest $request): void
    {
        abort_if($source->group_id !== $modelHasWebBlocks->group_id, 404);
        abort_unless($this->canReadSourceShop($request->user(), $source->shop), 403);

        $this->initialisationFromShop($modelHasWebBlocks->shop, $request);
        $this->handle($modelHasWebBlocks, $source);

        $this->flashUnmatchedLinks($request);
    }

    /**
     * @throws ValidationException
     */
    public function action(ModelHasWebBlocks $modelHasWebBlocks, ModelHasWebBlocks $source): ModelHasWebBlocks
    {
        $this->asAction = true;

        $this->initialisationFromShop($modelHasWebBlocks->shop, []);

        return $this->handle($modelHasWebBlocks, $source);
    }
}
