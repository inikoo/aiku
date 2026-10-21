<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct\UI;

use App\Enums\SupplyChain\SupplierProduct\SupplierProductStateEnum;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

class GetSupplierProductsShowcaseData
{
    use AsObject;

    /**
     * @param  Collection<int, SupplierProduct>  $supplierProducts
     * @return array<int, array{id: int, code: string, name: ?string, supplier_name: ?string, is_discontinued: bool, route: array{name: string, parameters: array<string, string>}, supplier_route: ?array{name: string, parameters: array<string, string>}}>
     */
    public function handle(Collection $supplierProducts): array
    {
        return EloquentCollection::make($supplierProducts)->loadMissing('supplier')
            ->map(fn (SupplierProduct $supplierProduct) => [
                'id'              => $supplierProduct->id,
                'code'            => $supplierProduct->code,
                'name'            => $supplierProduct->name,
                'supplier_name'   => $supplierProduct->supplier?->name,
                'is_discontinued' => $supplierProduct->state === SupplierProductStateEnum::DISCONTINUED,
                'route'           => [
                    'name'       => 'grp.supply-chain.supplier_products.show',
                    'parameters' => ['supplierProduct' => $supplierProduct->slug],
                ],
                'supplier_route'  => $supplierProduct->supplier ? [
                    'name'       => 'grp.supply-chain.suppliers.show',
                    'parameters' => ['supplier' => $supplierProduct->supplier->slug],
                ] : null,
            ])
            ->sortBy('is_discontinued')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, OrgSupplierProduct>  $orgSupplierProducts
     * @return array<int, array{id: int, code: string, name: ?string, supplier_name: ?string, is_discontinued: bool, route: array{name: string, parameters: array<string, string>}, supplier_route: ?array{name: string, parameters: array<string, string>}}>
     */
    public function forOrgSupplierProducts(Collection $orgSupplierProducts): array
    {
        return EloquentCollection::make($orgSupplierProducts)->loadMissing(['supplierProduct', 'orgSupplier.supplier', 'organisation'])
            ->map(fn (OrgSupplierProduct $orgSupplierProduct) => [
                'id'              => $orgSupplierProduct->id,
                'code'            => $orgSupplierProduct->supplierProduct->code,
                'name'            => $orgSupplierProduct->supplierProduct->name,
                'supplier_name'   => $orgSupplierProduct->orgSupplier?->supplier?->name,
                'is_discontinued' => $orgSupplierProduct->supplierProduct->state === SupplierProductStateEnum::DISCONTINUED,
                'route'           => [
                    'name'       => 'grp.org.procurement.org_supplier_products.show',
                    'parameters' => [
                        'organisation'       => $orgSupplierProduct->organisation->slug,
                        'orgSupplierProduct' => $orgSupplierProduct->slug,
                    ],
                ],
                'supplier_route'  => $orgSupplierProduct->orgSupplier ? [
                    'name'       => 'grp.org.procurement.org_suppliers.show',
                    'parameters' => [
                        'organisation' => $orgSupplierProduct->organisation->slug,
                        'orgSupplier'  => $orgSupplierProduct->orgSupplier->slug,
                    ],
                ] : null,
            ])
            ->sortBy('is_discontinued')
            ->values()
            ->all();
    }
}
