<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 May 2024 19:47:57 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier\Hydrators;

use App\Models\SupplyChain\Supplier;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Supplier $supplier): void
    {
        if ($supplier->trashed()) {
            return;
        }

        $supplier->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'        => $supplier->group_id,
                'sections'        => ['supply-chain'],
                'haystack_tier_1' => trim($supplier->name.' '.$supplier->email.' '.$supplier->company_name.' '.$supplier->contact_name),
            ]
        );
    }

}
