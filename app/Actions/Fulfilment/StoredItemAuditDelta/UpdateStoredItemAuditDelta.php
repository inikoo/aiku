<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAuditDelta;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Models\Fulfilment\StoredItemAuditDelta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateStoredItemAuditDelta extends OrgAction
{
    use WithActionUpdate;


    public function handle(StoredItemAuditDelta $storedItemAuditDelta, array $modelData): StoredItemAuditDelta
    {
        data_set($modelData, 'audited_at', now());

        if (!$this->asAction) {
            $palletStoredItem = DB::table('pallet_stored_items')
                ->where('pallet_id', $storedItemAuditDelta->pallet_id)
                ->where('stored_item_id', $storedItemAuditDelta->stored_item_id)->first();

            if ($palletStoredItem) {
                $originalQuantity = $palletStoredItem->quantity;

                if ($originalQuantity > $modelData['audited_quantity']) {
                    $type = StoredItemAuditDeltaTypeEnum::SUBTRACTION;
                } elseif ($originalQuantity < $modelData['audited_quantity']) {
                    $type = StoredItemAuditDeltaTypeEnum::ADDITION;
                } else {
                    $type = StoredItemAuditDeltaTypeEnum::NO_CHANGE;
                }
            } else {
                $originalQuantity = 0;
                $type             = StoredItemAuditDeltaTypeEnum::SET_UP;
            }
            data_set($modelData, 'original_quantity', $originalQuantity);
            data_set($modelData, 'audit_type', $type);
        }


        return $this->update($storedItemAuditDelta, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }


    public function rules(): array
    {
        return [
            'is_new_stored_item'           => ['sometimes', 'bool'],
            'is_stored_item_new_in_pallet' => ['sometimes', 'bool'],
            'audit_type'                   => ['sometimes', Rule::enum(StoredItemAuditDeltaTypeEnum::class)],
            'state'                        => ['sometimes', Rule::enum(StoredItemAuditDeltaStateEnum::class)],
            'audited_quantity'             => ['sometimes', 'integer', 'min:0'],
            'original_quantity'            => ['sometimes', 'integer', 'min:0'],
            'user_id'                      => ['required', Rule::exists('users', 'id')->where('group_id', $this->organisation->group_id)],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if (!$this->asAction) {
            $this->set('user_id', $request->user()->id);
        }
    }

    public function asController(StoredItemAuditDelta $storedItemAuditDelta, ActionRequest $request): StoredItemAuditDelta
    {
        $this->initialisationFromFulfilment($storedItemAuditDelta->storedItem->fulfilment, $request);

        return $this->handle($storedItemAuditDelta, $this->validatedData);
    }


    public function action(StoredItemAuditDelta $storedItemAuditDelta, $modelData): StoredItemAuditDelta
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($storedItemAuditDelta->storedItem->fulfilment, $modelData);

        return $this->handle($storedItemAuditDelta, $this->validatedData);
    }

}
