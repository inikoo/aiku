<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 11:54:59 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockAuditDelta;

use App\Actions\Catalogue\HasRentalAgreement;
use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateStoredItemAudits;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItemAudits;
use App\Actions\Fulfilment\StoredItemAudit\Search\StoredItemAuditRecordSearch;
use App\Actions\Fulfilment\WithDeliverableStoreProcessing;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateStoredItemAudits;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStoredItemAudits;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStoredItemAudits;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Inventory\LocationOrgStock;
use App\Models\Inventory\OrgStockAudit;
use App\Models\Inventory\Warehouse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgStockAuditDelta extends OrgAction
{
    use HasRentalAgreement;
    use WithDeliverableStoreProcessing;


    public Customer $customer;

    private bool $action = false;
    private FulfilmentCustomer $fulfilmentCustomer;

    public function handle(LocationOrgStock|OrgStockAudit $parent, array $modelData): StoredItemAudit
    {


        /** @var StoredItemAudit $storedItemAudit */
        $storedItemAudit = $parent->orgStockAuditDeltas()->create($modelData);
        $storedItemAudit->refresh();

        GroupHydrateStoredItemAudits::dispatch($storedItemAudit->group);
        OrganisationHydrateStoredItemAudits::dispatch($storedItemAudit->organisation);
        WarehouseHydrateStoredItemAudits::dispatch($storedItemAudit->warehouse);
        FulfilmentHydrateStoredItemAudits::dispatch($storedItemAudit->fulfilment);
        FulfilmentCustomerHydrateStoredItemAudits::dispatch($storedItemAudit->fulfilmentCustomer);

        StoredItemAuditRecordSearch::dispatch($storedItemAudit);


        return $storedItemAudit;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action) {
            return true;
        }

        if ($this->hasRentalAgreement($this->fulfilmentCustomer)) {
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        }

        return false;
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($this->fulfilment->warehouses()->count() == 1) {
            /** @var Warehouse $warehouse */
            $warehouse = $this->fulfilment->warehouses()->first();
            $this->fill(['warehouse_id' => $warehouse->id]);
        }
    }


    public function rules(): array
    {
        $rules = [];

        if (!request()->user() instanceof WebUser) {
            $rules = [
                'public_notes'   => ['sometimes', 'nullable', 'string', 'max:4000'],
                'internal_notes' => ['sometimes', 'nullable', 'string', 'max:4000'],
            ];
        }

        return [
            'warehouse_id'   => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('organisation_id', $this->organisation->id),
            ],
            'customer_notes' => ['sometimes', 'nullable', 'string'],
            ...$rules
        ];
    }


    public function asController(LocationOrgStock|OrgStockAudit $parent, ActionRequest $request): StoredItemAudit
    {

        $this->initialisation($parent->organisation, $request);

        return $this->handle($parent, $this->validatedData);
    }

    public function action(LocationOrgStock|OrgStockAudit $parent, $modelData): StoredItemAudit
    {



        $this->action = true;
        $this->initialisation($parent->organisation, $modelData);
        $this->setRawAttributes($modelData);

        return $this->handle($parent, $this->validatedData);
    }






}
