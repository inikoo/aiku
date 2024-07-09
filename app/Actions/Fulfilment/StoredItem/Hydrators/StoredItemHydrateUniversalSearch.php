<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\Hydrators;

use App\Models\Fulfilment\StoredItem;
use Lorisleiva\Actions\Concerns\AsAction;

class StoredItemHydrateUniversalSearch
{
    use AsAction;
    public string $jobQueue = 'universal-search';

    public function handle(StoredItem $storedItem): void
    {
        $storedItem->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'        => $storedItem->group_id,
                'organisation_id' => $storedItem->organisation_id,
                'section'         => 'fulfilment',
                'title'           => $storedItem->code,
                'description'     => $storedItem->notes,
                'slug'            => 'sto-'.$storedItem->code,
            ]
        );
    }

}
