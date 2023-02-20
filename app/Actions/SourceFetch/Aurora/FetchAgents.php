<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 08:37:56 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Agent\UpdateAgent;
use App\Models\Procurement\Agent;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchAgents extends FetchAction
{

    public string $commandSignature = 'fetch:agents {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Agent
    {
        if ($agentData = $tenantSource->fetchAgent($tenantSourceId)) {

            $owner=tenant();

            if ($agent = Agent::withTrashed()->where('source_agent_id', $agentData['agent']['source_agent_id'])
                ->first()) {
                $agent = UpdateAgent::run($agent, $agentData['agent']);
                UpdateAddress::run($agent->getAddress('contact'), $agentData['address']);
                $agent->location = $agent->getLocation();
                $agent->save();
            } else {
                $agent = StoreAgent::run(
                    owner:       $owner,
                    modelData:   $agentData['agent'],
                    addressData: $agentData['address']
                );
            }

            return $agent;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Agent Dimension')
            ->select('Agent Key as source_id')
            ->where('aiku_ignore', 'No')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')
            ->table('Agent Dimension')
            ->select('Agent Key as source_id')
            ->where('aiku_ignore', 'No')
            ->count();
    }

}
