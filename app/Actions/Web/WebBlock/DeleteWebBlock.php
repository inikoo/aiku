<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jun 2024 20:54:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlock;

use App\Actions\GrpAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Models\Web\WebBlock;
use App\Models\Web\Webpage;

class DeleteWebBlock extends GrpAction
{
    use HasWebAuthorisation;


    public function handle(Webpage $webpage, WebBlock $webBlock): void
    {
        $webBlock->delete();

        UpdateWebpageContent::run($webpage);
    }


    public function action(Webpage $webpage, WebBlock $webBlock, array $modelData): void
    {
        $this->asAction = true;

        $this->initialisation($webpage->group, $modelData);

        $this->handle($webpage, $webBlock, $modelData);
    }
}
