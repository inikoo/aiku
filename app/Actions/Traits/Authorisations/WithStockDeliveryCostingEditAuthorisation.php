<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Oct 2026 16:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\GoodsIn\StockDelivery;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

trait WithStockDeliveryCostingEditAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $stockDelivery = $this->costingStockDelivery($request);

        if (Arr::has($stockDelivery?->data ?? [], 'costing_reopened')) {
            return $stockDelivery->canUpdateCosting($request->user());
        }

        return $request->user()->authTo([
            "procurement.{$this->organisation->id}.edit",
            "accounting.{$this->organisation->id}.edit",
            "org-supervisor.{$this->organisation->id}.accounting",
        ]);
    }

    private function costingStockDelivery(ActionRequest $request): ?StockDelivery
    {
        return $request->route('stockDelivery')
            ?? $request->route('stockDeliveryItem')?->stockDelivery
            ?? $request->route('stockDeliveryCost')?->stockDelivery;
    }
}
