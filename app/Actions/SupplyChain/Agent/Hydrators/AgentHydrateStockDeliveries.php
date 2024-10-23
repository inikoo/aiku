<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:14:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent\Hydrators;

use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStatusEnum;
use App\Models\SupplyChain\Agent;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class AgentHydrateStockDeliveries
{
    use AsAction;
    private Agent $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->agent->id))->dontRelease()];
    }

    public function handle(Agent $agent): void
    {
        $stats = [
            'number_stock_deliveries' => $agent->purchaseOrders->count(),
        ];

        $stockDeliveryStateCounts = $agent->purchaseOrders()
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (StockDeliveryStateEnum::cases() as $productState) {
            $stats['number_stock_deliveries_state_'.$productState->snake()] = Arr::get($stockDeliveryStateCounts, $productState->value, 0);
        }

        $stockDeliveryStatusCounts =  $agent->purchaseOrders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (StockDeliveryStatusEnum::cases() as $stockDeliveryStatusEnum) {
            $stats['number_stock_deliveries_status_'.$stockDeliveryStatusEnum->snake()] = Arr::get($stockDeliveryStatusCounts, $stockDeliveryStatusEnum->value, 0);
        }

        $agent->stats()->update($stats);
    }


}
