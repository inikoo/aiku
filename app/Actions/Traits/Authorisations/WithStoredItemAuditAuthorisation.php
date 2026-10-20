<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use Lorisleiva\Actions\ActionRequest;

/**
 * Stored item audits are done by the fulfilment staff of the warehouse
 * the record is in. fulfilment.{id} permissions are keyed by warehouse, not by fulfilment.
 */
trait WithStoredItemAuditAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return isset($this->warehouse) && $request->user()->authTo([
            "fulfilment.{$this->warehouse->id}.edit",
            "supervisor-fulfilment.{$this->warehouse->id}",
        ]);
    }
}
