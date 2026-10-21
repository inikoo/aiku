<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use Lorisleiva\Actions\ActionRequest;

/**
 * The PDF and CSV of a stock delivery are read by procurement and by the goods in staff who receive it.
 */
trait WithStockDeliveryDocumentAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $warehousePermissions = $this->organisation->warehouses()->pluck('id')
            ->map(fn (int $warehouseId) => "incoming.$warehouseId.view")
            ->all();

        return $request->user()->authTo(["procurement.{$this->organisation->id}.view", ...$warehousePermissions]);
    }
}
