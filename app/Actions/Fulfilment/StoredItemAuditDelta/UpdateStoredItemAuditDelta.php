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
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateStoredItemAuditDelta extends OrgAction
{
    use WithActionUpdate;



    public function handle(StoredItemAuditDelta $storedItemAuditDelta, array $modelData): StoredItemAuditDelta
    {
        return $this->update($storedItemAuditDelta, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'audited_at' => ['sometimes', 'date'],
            'audit_type'          => ['sometimes', Rule::enum(StoredItemAuditDeltaTypeEnum::class)],
            'state'          => ['sometimes', Rule::enum(StoredItemAuditDeltaStateEnum::class)],
            'audited_quantity' => ['sometimes', 'integer', 'min:0']
        ];
    }

    public function asController(StoredItemAuditDelta $storedItemAuditDelta, ActionRequest $request): StoredItemAuditDelta
    {
        $this->initialisationFromFulfilment($storedItemAuditDelta->storedItem->fulfilment, $request);
        return $this->handle($storedItemAuditDelta, $this->validatedData);
    }


    public function action(StoredItemAuditDelta $storedItemAuditDelta, $modelData): StoredItemAuditDelta
    {
        $this->initialisationFromFulfilment($storedItemAuditDelta->storedItem->fulfilment, $modelData);

        return $this->handle($storedItemAuditDelta, $this->validatedData);
    }

}
