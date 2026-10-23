<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\Dispatching\DeliveryNoteItem;
use Lorisleiva\Actions\ActionRequest;

trait WithDeliveryNoteItemReadAuthorisation
{
    /**
     * A delivery note line is read by the staff of its warehouse and by the order staff of its
     * shop, who see the same lines on the order and the waiting for customer service pages.
     */
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $deliveryNoteItem = $request->route('deliveryNoteItem');
        if (!$deliveryNoteItem instanceof DeliveryNoteItem) {
            return false;
        }

        $deliveryNote = $deliveryNoteItem->deliveryNote;
        if (!$deliveryNote) {
            return false;
        }

        $warehouseId = $deliveryNote->warehouse_id;

        return $request->user()->authTo([
            "org-admin.$deliveryNote->organisation_id",
            "dispatching.$warehouseId.view",
            "fulfilment.$warehouseId.view",
            "returns.$warehouseId",
            "incoming.$warehouseId.view",
            "orders.$deliveryNoteItem->shop_id.view",
        ]);
    }
}
