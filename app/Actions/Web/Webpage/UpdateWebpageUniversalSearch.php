<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 19:08:03 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\HydrateModel;

use App\Actions\Web\Webpage\Hydrators\WebpageHydrateUniversalSearch;
use App\Models\Web\Webpage;
use Illuminate\Support\Collection;

class UpdateWebpageUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'webpage:search {organisations?*} {--s|slugs=} ';


    public function handle(Webpage $webpage): void
    {
        WebpageHydrateUniversalSearch::run($webpage);
    }

    protected function getModel(string $slug): Webpage
    {
        return Webpage::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Webpage::withTrashed()->get();
    }
}
