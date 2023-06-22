<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 00:51:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent\Hydrators;

use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class AgentHydrateSuppliers implements ShouldBeUnique
{
    use AsAction;


    public function handle(Agent $agent): void
    {
        $stats = [
            'number_suppliers'          => Supplier::where('agent_id', $agent->id)->where('status', true)->count(),
            'number_archived_suppliers' => Supplier::where('agent_id', $agent->id)->where('status', false)->count(),
        ];
        $agent->stats->update($stats);
    }

    public function getJobUniqueId(Agent $agent): int
    {
        return $agent->id;
    }
}
