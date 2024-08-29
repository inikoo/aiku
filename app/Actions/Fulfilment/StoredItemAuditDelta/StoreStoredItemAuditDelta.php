<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 18:06:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAuditDelta;

use App\Actions\Catalogue\HasRentalAgreement;
use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateStoredItemAudits;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItemAudits;
use App\Actions\Fulfilment\StoredItemAudit\Redirect;
use App\Actions\Fulfilment\StoredItemAudit\Search\StoredItemAuditRecordSearch;
use App\Actions\Fulfilment\WithDeliverableStoreProcessing;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateStoredItemAudits;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStoredItemAudits;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStoredItemAudits;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreStoredItemAuditDelta extends OrgAction
{
    use HasRentalAgreement;
    use WithDeliverableStoreProcessing;


    public Customer $customer;

    private bool $action = false;
    private FulfilmentCustomer $fulfilmentCustomer;

    public function handle(StoredItemAudit $storedItemAudit, array $modelData): StoredItemAudit
    {
        $modelData = $this->processData(
            $modelData,
            $storedItemAudit,
            SerialReferenceModelEnum::STORED_ITEM_AUDIT
        );

        /** @var StoredItemAudit $storedItemAudit */
        $storedItemAudit = $storedItemAudit->deltas()->create($modelData);
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


    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): StoredItemAudit
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, $modelData): StoredItemAudit
    {
        $this->action = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function jsonResponse(StoredItemAudit $storedItemAudit): array
    {
        return [
            'route' => [
                'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.show',
                'parameters' => [
                    'organisation'       => $storedItemAudit->organisation->slug,
                    'fulfilment'         => $storedItemAudit->fulfilment->slug,
                    'fulfilmentCustomer' => $storedItemAudit->fulfilmentCustomer->slug,
                    'storedItemAudit'    => $storedItemAudit->reference
                ]
            ]
        ];
    }

    public function htmlResponse(StoredItemAudit $storedItemAudit, ActionRequest $request): Response
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.stored-item-audits.show', [
            'organisation'       => $storedItemAudit->organisation->slug,
            'fulfilment'         => $storedItemAudit->fulfilment->slug,
            'fulfilmentCustomer' => $storedItemAudit->fulfilmentCustomer->slug,
            'storedItemAudit'    => $storedItemAudit->slug
        ]);
    }


}
