<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 18:06:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAuditDelta;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaStateEnum;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Fulfilment\StoredItemAuditDelta;
use Illuminate\Validation\Rule;

class StoreStoredItemAuditDelta extends OrgAction
{
    public function handle(StoredItemAudit $storedItemAudit, array $modelData): StoredItemAuditDelta
    {
        data_set($modelData, 'group_id', $storedItemAudit->group_id);
        data_set($modelData, 'organisation_id', $storedItemAudit->organisation_id);

        return $storedItemAudit->deltas()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'pallet_id' => ['required', 'integer', 'exists:pallets,id'],
            'stored_item_id' => ['required', 'integer', 'exists:stored_items,id'],
            'audited_at' => ['required', 'date'],
            'audit_type'          => ['required', Rule::enum(StoredItemAuditDeltaTypeEnum::class)],
            'state'          => ['required', Rule::enum(StoredItemAuditDeltaStateEnum::class)],
            'audited_quantity' => ['required', 'integer', 'min:0'],
            'original_quantity' => ['required', 'integer', 'min:0'],
            'is_new_stored_item' => ['required', 'bool'],
            'is_stored_item_new_in_pallet' => ['required', 'bool']
        ];
    }

    public function action(StoredItemAudit $storedItemAudit, $modelData): StoredItemAuditDelta
    {
        $this->initialisationFromFulfilment($storedItemAudit->fulfilment, $modelData);

        return $this->handle($storedItemAudit, $this->validatedData);
    }


}
