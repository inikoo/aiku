<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\SupplyChain\SupplierStockFamily\UpdateSupplierStockFamily;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Goods\StockFamily;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierStockFamily;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

/**
 * An agent sets what holds for a family when bought from one of its suppliers: MOQ, lead time, packaging terms and production calendar.
 */
class UpdateAgentSupplierStockFamily extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementEditAuthorisation;

    public function handle(Supplier $supplier, StockFamily $stockFamily, array $modelData): SupplierStockFamily
    {
        return UpdateSupplierStockFamily::make()->action($supplier, $stockFamily, $modelData);
    }

    public function rules(): array
    {
        return [
            'minimum_carton_order'     => ['sometimes', 'nullable'],
            'estimated_lead_time_days' => ['sometimes', 'nullable'],
            'packaging_terms'          => ['sometimes', 'array'],
            'production_calendar'      => ['sometimes', 'array'],
        ];
    }

    public function asController(Organisation $organisation, Supplier $supplier, StockFamily $stockFamily, ActionRequest $request): SupplierStockFamily
    {
        $this->initialisation($organisation, $request);
        abort_unless($this->organisationAgentSupplies($supplier), 404);

        return $this->handle($supplier, $stockFamily, $this->validatedData);
    }
}
