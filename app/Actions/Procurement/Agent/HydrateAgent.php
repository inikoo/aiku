<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 00:57:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\HydrateModel;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSupplierProducts;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Models\Procurement\Agent;
use Illuminate\Support\Collection;

class HydrateAgent extends HydrateModel
{
    public string $commandSignature = 'hydrate:agents {tenants?*} {--i|id=} ';

    public function handle(Agent $agent): void
    {
        AgentHydrateSuppliers::run($agent);
        AgentHydrateSupplierProducts::run($agent);
    }


    protected function getModel(int $id): Agent
    {
        return Agent::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Agent::withTrashed()->get();
    }
}
