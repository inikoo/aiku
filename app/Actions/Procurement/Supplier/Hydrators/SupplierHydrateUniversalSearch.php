<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Supplier\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\Procurement\Supplier;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydrateUniversalSearch
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(Supplier $supplier): void
    {
        $supplier->universalSearch()->updateOrCreate(
            [],
            [
                'section'        => 'procurement',
                'title'          => trim($supplier->name.' '.$supplier->email.' '.$supplier->company_name.' '.$supplier->contact_name),
                'description'    => ''
            ]
        );
    }

}
