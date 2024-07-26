<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItemAudit\StoredItemAuditStateEnum;
use App\Http\Resources\Fulfilment\StoredItemAuditsResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Rules\AlphaDashDotSpaceSlashParenthesisPlus;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateStoredItemAudit extends OrgAction
{
    use WithActionUpdate;

    private FulfilmentCustomer $fulfilmentCustomer;
    private StoredItemAudit $storedItemAudit;

    public function handle(StoredItemAudit $storedItemAudit, array $modelData): StoredItemAudit
    {
        $storedItemAudit = $this->update($storedItemAudit, $modelData, ['data']);

        // Hydrators

        return $storedItemAudit;
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
            'state'     => ['sometimes', 'required', Rule::enum(StoredItemAuditStateEnum::class)],
        ];
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, StoredItemAudit $storedItemAudit, ActionRequest $request): StoredItemAudit
    {
        $this->fulfilmentCustomer = $storedItemAudit->fulfilmentCustomer;
        $this->storedItemAudit    = $storedItemAudit;
        $this->initialisationFromFulfilment($storedItemAudit->fulfilment, $request);

        return $this->handle($storedItemAudit, $this->validatedData);
    }

    public function jsonResponse(StoredItemAudit $storedItem): StoredItemAuditsResource
    {
        return new StoredItemAuditsResource($storedItem);
    }
}
