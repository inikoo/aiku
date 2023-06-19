<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Supplier\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Procurement\Supplier;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Supplier $supplier): void
    {
        $supplier->universalSearch()->create(
            [
                'section' => 'Procurement',
                'route'   => json_encode([
                    'name'      => 'procurement.agents.show',
                    'arguments' => [
                        $supplier->slug
                    ]
                ]),
                'icon'           => 'fa-person-dolly',
                'primary_term'   => $supplier->name.' '.$supplier->email,
                'secondary_term' => $supplier->company_name.' '.$supplier->contact_name
            ]
        );
    }

}
