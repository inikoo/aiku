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
use App\Models\Web\WebBlock;
use App\Models\Web\Webpage;
use Illuminate\Http\Resources\Json\JsonResource;

class DeleteModelHasWebBlocks extends GrpAction
{
    use HasWebAuthorisation;


    public function handle(Webpage $webpage, WebBlock $webBlock): Webpage
    {
        $webpage->webBlocks()->detach([$webBlock->id]);

        UpdateWebpageContent::run($webpage);

        return $webpage;
    }

    public function jsonResponse(Webpage $webpage): JsonResource
    {
        return WebpageResource::make($webpage);
    }

    public function action(Webpage $webpage, WebBlock $webBlock, array $modelData): void
    {
        $this->asAction = true;

        $this->initialisation($webpage->group, $modelData);

        $this->handle($webpage, $webBlock);
    }
}
