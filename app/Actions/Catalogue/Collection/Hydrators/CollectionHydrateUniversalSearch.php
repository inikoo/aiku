<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 06:49:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Collection\Hydrators;

use App\Models\Catalogue\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CollectionHydrateUniversalSearch
{
    use AsAction;


    public function handle(Collection $collection): void
    {
        $collection->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $collection->group_id,
                'organisation_id'   => $collection->organisation_id,
                'organisation_slug' => $collection->organisation->slug,
                'shop_id'           => $collection->shop_id,
                'shop_slug'         => $collection->shop->slug,
                'section'           => 'shops',
                'title'             => $collection->name,
                'description'       => $collection->code
            ]
        );
    }

}
