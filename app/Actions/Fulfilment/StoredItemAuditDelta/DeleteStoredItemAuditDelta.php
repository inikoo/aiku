<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAuditDelta;

use App\Actions\Fulfilment\StoredItemAudit\Hydrators\StoredItemAuditHydrateDeltas;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\StoredItemAuditDelta;
use Lorisleiva\Actions\ActionRequest;

class DeleteStoredItemAuditDelta extends OrgAction
{
    use WithActionUpdate;

    public function handle(StoredItemAuditDelta $storedItemAuditDelta): bool
    {

        $storedItemAuditDelta->delete();
        StoredItemAuditHydrateDeltas::dispatch($storedItemAuditDelta->storedItemAudit);

        return true;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function asController(StoredItemAuditDelta $storedItemAuditDelta, ActionRequest $request): bool
    {
        $this->initialisationFromFulfilment($storedItemAuditDelta->storedItemAudit->fulfilment, $request);

        return $this->handle($storedItemAuditDelta);
    }

    public function action(StoredItemAuditDelta $storedItemAuditDelta, $modelData): bool
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($storedItemAuditDelta->storedItemAudit->fulfilment, $modelData);

        return $this->handle($storedItemAuditDelta);
    }


}
