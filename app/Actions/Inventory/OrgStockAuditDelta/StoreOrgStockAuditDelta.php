<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 11:54:59 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockAuditDelta;

use App\Actions\Catalogue\HasRentalAgreement;

use App\Actions\Fulfilment\WithDeliverableStoreProcessing;
use App\Actions\OrgAction;
use App\Enums\Inventory\OrgStockAuditDelta\OrgStockAuditDeltaTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Inventory\LocationOrgStock;
use App\Models\Inventory\OrgStockAudit;
use App\Models\Inventory\OrgStockAuditDelta;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgStockAuditDelta extends OrgAction
{
    use HasRentalAgreement;
    use WithDeliverableStoreProcessing;


    public Customer $customer;

    private bool $action = false;


    public function handle(LocationOrgStock|OrgStockAudit $parent, array $modelData): OrgStockAuditDelta
    {
        data_set($modelData, 'audited_at', now(), overwrite: false);
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'warehouse_id', $parent->warehouse_id);
        data_set($modelData, 'org_stock_id', $parent->org_stock_id);
        data_set($modelData, 'location_id', $parent->location_id);
        data_set($modelData, 'type', OrgStockAuditDeltaTypeEnum::ADDITION->value);


        if($parent instanceof OrgStockAudit) {
            $orgStockAuditDelta = $parent->orgStockAuditDeltas()->create($modelData);
        } else {
            $orgStockAuditDelta=OrgStockAuditDelta::create($modelData);
        }

        return $orgStockAuditDelta;
    }


    public function rules(): array
    {
        return [
            'original_quantity' => ['required', 'numeric'],
            'audited_quantity'  => ['required', 'numeric'],
        ];
    }


    public function asController(LocationOrgStock|OrgStockAudit $parent, ActionRequest $request): OrgStockAuditDelta
    {
        $this->initialisation($parent->organisation, $request);

        return $this->handle($parent, $this->validatedData);
    }

    public function action(LocationOrgStock|OrgStockAudit $parent, $modelData): OrgStockAuditDelta
    {
        $this->action = true;
        $this->initialisation($parent->organisation, $modelData);
        $this->setRawAttributes($modelData);

        return $this->handle($parent, $this->validatedData);
    }


}
