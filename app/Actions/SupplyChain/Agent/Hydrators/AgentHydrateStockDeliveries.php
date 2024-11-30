<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:14:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStatusEnum;
use App\Models\Procurement\StockDelivery;
use App\Models\SupplyChain\Agent;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class AgentHydrateStockDeliveries
{
    use AsAction;
    use WithEnumStats;
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
            'number_stock_deliveries' => $agent->stockDeliveries->count(),
        ];

        $stats = array_merge($stats, $this->getEnumStats(
            model:'stock_deliveries',
            field: 'state',
            enum: StockDeliveryStateEnum::class,
            models: StockDelivery::class,
            where: function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            }
        ));

        $stats = array_merge($stats, $this->getEnumStats(
            model:'stock_deliveries',
            field: 'status',
            enum: StockDeliveryStatusEnum::class,
            models: StockDelivery::class,
            where: function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            }
        ));

        $agent->stats()->update($stats);
    }


}
