<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Jun 2024 12:59:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\ModelHasWebBlocks;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Http\Resources\Web\WebpageResource;
use App\Models\Dropshipping\ModelHasWebBlocks;
use Lorisleiva\Actions\ActionRequest;

class UpdateModelHasWebBlocks extends OrgAction
{
    use HasWebAuthorisation;
    use WithActionUpdate;


    public function handle(ModelHasWebBlocks $modelHasWebBlocks, array $modelData): ModelHasWebBlocks
    {
        $this->update($modelHasWebBlocks->webBlock, $modelData, ['data', 'layout']);
        $modelHasWebBlocks->refresh();
        UpdateWebpageContent::run($modelHasWebBlocks->webpage);

        return $modelHasWebBlocks;

    }

    public function asController(ModelHasWebBlocks $modelHasWebBlocks, ActionRequest $request): ModelHasWebBlocks
    {
        return $this->handle($modelHasWebBlocks, $request->all());
    }

    public function rules(): array
    {
        return [
            'layout'       => ['sometimes', 'array'],
        ];
    }


    public function action(ModelHasWebBlocks $modelHasWebBlocks, $modelData): ModelHasWebBlocks
    {
        $this->asAction = true;

        $this->initialisation($modelHasWebBlocks->organisation, $modelData);

        return $this->handle($modelHasWebBlocks, $this->validatedData);
    }

    public function jsonResponse(ModelHasWebBlocks $modelHasWebBlocks): WebpageResource
    {
        $modelHasWebBlocks->webpage->refresh();
        return new WebpageResource($modelHasWebBlocks->webpage);
    }

}
