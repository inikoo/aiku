<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:43:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\TopUp\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\TopUp;
use App\Models\Inventory\Location;
use Illuminate\Support\Collection;

class ReindexTopUpSearch extends HydrateModel
{
    public string $commandSignature = 'topup:search {organisations?*} {--s|slugs=}';


    public function handle(TopUp $topUp): void
    {
        TopUpRecordSearch::run($topUp);
    }


    protected function getModel(string $slug): TopUp
    {
        return TopUp::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return TopUp::all();
    }
}
