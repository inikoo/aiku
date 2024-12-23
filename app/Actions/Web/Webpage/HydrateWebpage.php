<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Feb 2024 23:43:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\HydrateModel;
use App\Actions\Web\Webpage\Hydrators\WebpageHydrateRedirects;
use App\Actions\Web\Webpage\Hydrators\WebpageHydrateChildWebpages;
use App\Models\Web\Webpage;
use Illuminate\Support\Collection;

class HydrateWebpage extends HydrateModel
{
    public string $commandSignature = 'hydrate:webpages {organisations?*} {--s|slugs=} ';


    public function handle(Webpage $webpage): void
    {
        WebpageHydrateChildWebpages::run($webpage);
        WebpageHydrateRedirects::run($webpage);
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
