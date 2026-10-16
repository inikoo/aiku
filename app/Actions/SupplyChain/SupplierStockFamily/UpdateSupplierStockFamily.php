<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierStockFamily;

use App\Actions\OrgAction;
use App\Actions\SupplyChain\SupplierProduct\WithPackagingTermsRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\SupplyChain\SupplierProduct\ProductionCalendarMonthEnum;
use App\Models\Goods\StockFamily;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierStockFamily;
use Illuminate\Validation\Rule;

class UpdateSupplierStockFamily extends OrgAction
{
    use WithActionUpdate;
    use WithPackagingTermsRules;

    public function handle(Supplier $supplier, StockFamily $stockFamily, array $modelData): SupplierStockFamily
    {
        $supplierStockFamily = $supplier->supplierStockFamilies()->firstOrCreate(
            ['stock_family_id' => $stockFamily->id],
            ['group_id' => $supplier->group_id]
        );

        return $this->update($supplierStockFamily, $modelData, ['packaging_terms', 'production_calendar']);
    }

    public function rules(): array
    {
        return array_merge([
            'minimum_carton_order'     => ['sometimes', 'nullable', 'integer', 'min:1'],
            'estimated_lead_time_days' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365'],
            'production_calendar'      => ['sometimes', 'array:'.implode(',', range(1, 12))],
            'production_calendar.*'    => ['nullable', Rule::enum(ProductionCalendarMonthEnum::class)],
        ], $this->packagingTermsRules());
    }

    public function action(Supplier $supplier, StockFamily $stockFamily, array $modelData): SupplierStockFamily
    {
        $this->asAction = true;
        $this->initialisationFromGroup($supplier->group, $modelData);

        return $this->handle($supplier, $stockFamily, $this->validatedData);
    }
}
