<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:16:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent;

use App\Actions\HydrateModel;

use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSupplierProducts;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSuppliers;
use App\Models\SupplyChain\Agent;
use Illuminate\Support\Collection;

class HydrateAgent extends HydrateModel
{
    public string $commandSignature = 'hydrate:agents {organisations?*} {--i|id=} ';

    public function handle(Agent $agent): void
    {
        AgentHydrateSuppliers::run($agent);
        AgentHydrateSupplierProducts::run($agent);
    }


    protected function getModel(string $slug): Agent
    {
        return Agent::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Agent::withTrashed()->get();
    }
}
