<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:14:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateSupplierProducts;
use App\Models\SupplyChain\Agent;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class AgentHydrateSupplierProducts
{
    use AsAction;
    use WithHydrateSupplierProducts;

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
        $stats=$this->getSupplierProductsStats($agent);
        $agent->stats()->update($stats);
    }


}
