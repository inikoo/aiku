<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Sept 2024 11:46:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Portfolio\Search;

use App\Actions\HydrateModel;
use App\Models\Dropshipping\Portfolio;
use Illuminate\Support\Collection;

class ReindexPortfolioSearch extends HydrateModel
{
    public string $commandSignature = 'search:portfolios {organisations?*} {--s|slugs=}';


    public function handle(Portfolio $portfolio): void
    {
        PortfolioRecordSearch::run($portfolio);
    }

    protected function getModel(string $slug): Portfolio
    {
        return Portfolio::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Portfolio::all();
    }
}
