<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 18:06:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAuditDelta;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Models\Fulfilment\StoredItem;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Fulfilment\StoredItemAuditDelta;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreStoredItemAuditDelta extends OrgAction
{
    public function handle(StoredItemAudit $storedItemAudit, array $modelData): StoredItemAuditDelta
    {
        data_set($modelData, 'group_id', $storedItemAudit->group_id);
        data_set($modelData, 'organisation_id', $storedItemAudit->organisation_id);
        data_set($modelData, 'audited_at', now());

        $auditType       = StoredItemAuditDeltaTypeEnum::ADDITION;
        $isNewStoredItem = false;
        $storedItem      = StoredItem::where('id', $modelData['stored_item_id'])->first();

        if (in_array($storedItem->state, [StoredItemStateEnum::SUBMITTED, StoredItemStateEnum::IN_PROCESS])) {
            $auditType       = StoredItemAuditDeltaTypeEnum::SET_UP;
            $isNewStoredItem = true;
        }


        data_set($modelData, 'audit_type', $auditType);
        data_set($modelData, 'state', StoredItemAuditDeltaStateEnum::IN_PROCESS);
        data_set($modelData, 'original_quantity', 0);

        data_set($modelData, 'is_new_stored_item', $isNewStoredItem);
        data_set($modelData, 'is_stored_item_new_in_pallet', true);


        return $storedItemAudit->deltas()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'pallet_id'        => ['required', 'integer', 'exists:pallets,id'],
            'stored_item_id'   => ['required', 'integer', 'exists:stored_items,id'],
            'audited_quantity' => ['required', 'integer', 'min:0'],
            'user_id'          => ['sometimes', 'required', Rule::exists('users', 'id')->where('group_id', $this->organisation->group_id)],

        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if (!$this->asAction) {
            $this->set('user_id', $request->user()->id);
        }
    }

    public function asController(StoredItemAudit $storedItemAudit, ActionRequest $request): StoredItemAuditDelta
    {
        $this->initialisationFromFulfilment($storedItemAudit->fulfilment, $request);

        return $this->handle($storedItemAudit, $this->validatedData);
    }

    public function action(StoredItemAudit $storedItemAudit, $modelData): StoredItemAuditDelta
    {
        $this->initialisationFromFulfilment($storedItemAudit->fulfilment, $modelData);

        return $this->handle($storedItemAudit, $this->validatedData);
    }


}
