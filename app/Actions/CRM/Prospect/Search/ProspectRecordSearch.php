<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:04:40 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Search;

use App\Models\CRM\Prospect;
use Lorisleiva\Actions\Concerns\AsAction;

class ProspectRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Prospect $prospect): void
    {
        if ($prospect->trashed()) {
            $prospect->universalSearch()->delete();
            return;
        }

        $prospect->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $prospect->group_id,
                'organisation_id'   => $prospect->organisation_id,
                'organisation_slug' => $prospect->organisation->slug,
                'shop_id'           => $prospect->shop_id,
                'shop_slug'         => $prospect->shop->slug,
                'sections'          => ['crm'],
                'haystack_tier_1'   => trim($prospect->name.' '.$prospect->contact_name),
                'haystack_tier_2'   => trim($prospect->email.' '.$prospect->company_name)
            ]
        );
    }

}
