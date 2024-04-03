<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 10:30:08 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace;

use App\Actions\HumanResources\Workplace\Hydrators\WorkplaceHydrateUniversalSearch;
use App\Actions\HydrateModel;
use App\Models\HumanResources\Workplace;
use Illuminate\Support\Collection;

class UpdateWorkplaceUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'workplace:search {organisations?*} {--s|slugs=}';


    public function handle(Workplace $workplace): void
    {
        WorkplaceHydrateUniversalSearch::run($workplace);
    }


    protected function getModel(string $slug): Workplace
    {
        return Workplace::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Workplace::get();
    }
}
