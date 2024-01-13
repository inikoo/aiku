<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 01:38:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Query;

use App\Actions\Helpers\Query\Hydrators\QueryHydrateCount;
use App\Actions\HydrateModel;
use App\Models\Helpers\Query;
use Illuminate\Support\Collection;

class HydrateQueries extends HydrateModel
{
    public function handle(Query $query): void
    {
        QueryHydrateCount::run($query);

    }

    public string $commandSignature = 'hydrate:queries {slugs?*}';

    protected function getModel(string $slug): Query
    {
        return Query::firstWhere($slug);
    }

    protected function getAllModels(): Collection
    {
        return Query::get();
    }

}
