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
        $supplier->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'       => $supplier->group_id,
                'section'        => 'supply-chain',
                'title'          => trim($supplier->name.' '.$supplier->email.' '.$supplier->company_name.' '.$supplier->contact_name),
                'description'    => ''
            ]
        );
    }

}
