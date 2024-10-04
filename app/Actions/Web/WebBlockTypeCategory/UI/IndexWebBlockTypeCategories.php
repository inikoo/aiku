<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:12:19 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlockTypeCategory\UI;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Web\WebBlockTypeCategory;
use App\Services\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;

class IndexWebBlockTypeCategories
{
    use WithActionUpdate;


    public function handle(string $type): Collection
    {
        $query = QueryBuilder::for(WebBlockTypeCategory::class);

        $query->where('slug', $type);

        return $query->get();
    }
}
