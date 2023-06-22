<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 May 2023 17:09:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Group\Hydrators;

use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Group;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateProcurement implements ShouldBeUnique
{
    use AsAction;

    public function handle(Group $group): void
    {
        $stats = [
            'number_suppliers'          => Supplier::where('suppliers.status', true)->count(),
            'number_archived_suppliers' => Supplier::where('suppliers.status', false)->count(),
            'number_agents'             => Agent::where('agents.status', true)->count(),
            'number_archived_agents'    => Agent::where('agents.status', false)->count(),

            'supplier_products_count'  => SupplierProduct::count(),
            'number_supplier_products' => SupplierProduct::where('supplier_products.state', '!=', SupplierProductStateEnum::DISCONTINUED)
                ->count(),
        ];
        $group->procurementStats->update($stats);
    }

    public function getJobUniqueId(Group $group): int
    {
        return $group->id;
    }


}
