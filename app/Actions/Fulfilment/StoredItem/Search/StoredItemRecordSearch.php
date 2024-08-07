<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:55:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\Search;

use App\Models\Fulfilment\StoredItem;
use Lorisleiva\Actions\Concerns\AsAction;

class StoredItemRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(StoredItem $storedItem): void
    {
        $storedItem->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $storedItem->group_id,
                'organisation_id'   => $storedItem->organisation_id,
                'sections'          => ['fulfilment'],
                'haystack_tier_1'   => $storedItem->reference,
                'haystack_tier_2'   => $storedItem->notes,
                'keyword'           => $storedItem->slug,
                'keyword_2'         => $storedItem->reference,
            ]
        );
    }

}
