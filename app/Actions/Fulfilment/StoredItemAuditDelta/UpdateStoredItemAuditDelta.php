<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAuditDelta;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateStoredItemAudits;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItemAudits;
use App\Actions\Fulfilment\StoredItemAudit\Search\StoredItemAuditRecordSearch;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateStoredItemAudits;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStoredItemAudits;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStoredItemAudits;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Fulfilment\StoredItemAuditDelta;
use App\Rules\AlphaDashDotSpaceSlashParenthesisPlus;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateStoredItemAuditDelta extends OrgAction
{
    use WithActionUpdate;

    private FulfilmentCustomer $fulfilmentCustomer;
    private StoredItemAudit $storedItemAudit;
    /**
     * @var \App\Models\Fulfilment\StoredItemAuditDelta
     */
    private StoredItemAuditDelta $storedItemAuditDelta;

    public function handle(StoredItemAuditDelta $storedItemAuditDelta, array $modelData): StoredItemAuditDelta
    {
        /** @var StoredItemAuditDelta $storedItemAuditDelta */
        $storedItemAuditDelta = $this->update($storedItemAuditDelta, $modelData, ['data']);

        GroupHydrateStoredItemAudits::dispatch($storedItemAuditDelta->group);
        OrganisationHydrateStoredItemAudits::dispatch($storedItemAuditDelta->organisation);
        WarehouseHydrateStoredItemAudits::dispatch($storedItemAuditDelta->storedItemAudit->warehouse);
        FulfilmentHydrateStoredItemAudits::dispatch($storedItemAuditDelta->storedItemAudit->fulfilment);
        FulfilmentCustomerHydrateStoredItemAudits::dispatch($storedItemAuditDelta->storedItemAudit->fulfilmentCustomer);

        StoredItemAuditRecordSearch::dispatch($storedItemAuditDelta->storedItemAudit);

        return $storedItemAuditDelta;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'reference' => [
                'sometimes',
                'required',
                'max:128',
                new AlphaDashDotSpaceSlashParenthesisPlus(),
                new IUnique(
                    table: 'stored_item_audits',
                    extraConditions: [
                        [
                            'column' => 'fulfilment_customer_id',
                            'value'  => $this->fulfilmentCustomer->id,
                        ],
                        ['column' => 'id', 'value' => $this->storedItemAudit->id, 'operator' => '!=']

                    ]
                )

            ],
            'audited_quantity' => ['sometimes', 'numeric']
        ];
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, StoredItemAuditDelta $storedItemAuditDelta, ActionRequest $request): StoredItemAuditDelta
    {
        $this->storedItemAudit = $storedItemAuditDelta->storedItemAudit;
        $this->fulfilmentCustomer      = $storedItemAuditDelta->storedItem->fulfilmentCustomer;
        $this->storedItemAuditDelta    = $storedItemAuditDelta;
        $this->initialisationFromFulfilment($storedItemAuditDelta->storedItem->fulfilment, $request);

        return $this->handle($storedItemAuditDelta, $this->validatedData);
    }
}
