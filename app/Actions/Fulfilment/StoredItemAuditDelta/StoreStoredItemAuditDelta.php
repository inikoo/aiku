<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 18:06:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAuditDelta;

use App\Actions\OrgAction;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Fulfilment\StoredItemAuditDelta;

class StoreStoredItemAuditDelta extends OrgAction
{
    public function handle(StoredItemAudit $storedItemAudit, array $modelData): StoredItemAuditDelta
    {
        return $storedItemAudit->deltas()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'stored_item_id' => 'required|exists:stored_items,id',
            'quantity'       => 'required|integer|min:0',
            'state'          => 'required|in:pending,completed',
            'audit_type'     => 'required|in:set_up,addition,subtraction,no_change'
        ];
    }

    public function action(StoredItemAudit $storedItemAudit, $modelData): StoredItemAuditDelta
    {
        $this->initialisationFromFulfilment($storedItemAudit->fulfilment, $modelData);

        return $this->handle($storedItemAudit, $this->validatedData);
    }


}
