<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 13:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\SupplyChain\SupplierProduct\UpdateSupplierProduct;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrgSupplierProductMinimumCartonOrder extends OrgAction
{
    use WithProcurementEditAuthorisation;

    public function handle(OrgSupplierProduct $orgSupplierProduct, array $modelData): SupplierProduct
    {
        return UpdateSupplierProduct::make()->action($orgSupplierProduct->supplierProduct, $modelData);
    }

    public function rules(): array
    {
        return [
            'minimum_carton_order' => ['present', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function asController(OrgSupplierProduct $orgSupplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($orgSupplierProduct->organisation, $request);

        return $this->handle($orgSupplierProduct, $this->validatedData);
    }
}
