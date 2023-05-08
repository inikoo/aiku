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
use App\Actions\Tenancy\Tenant\AttachAgent;
use App\Models\Procurement\Agent;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAgents extends FetchAction
{
    public string $commandSignature = 'fetch:agents {tenants?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Agent
    {
        if ($agentData = $tenantSource->fetchAgent($tenantSourceId)) {
            $tenant = app('currentTenant');

            if ($agent = Agent::withTrashed()->where('source_id', $agentData['agent']['source_id'])->where('source_type', $tenant->slug)->first()) {
                $agent = UpdateAgent::run($agent, $agentData['agent']);
                UpdateAddress::run($agent->getAddress('contact'), $agentData['address']);
                $agent->location = $agent->getLocation();
                $agent->save();
            } else {
                $agent = Agent::withTrashed()->where('code', $agentData['agent']['code'])->first();
                if ($agent) {
                    AttachAgent::run($tenant, $agent, ['source_id' => $agentData['agent']['source_id']]);
                } else {
                    $agentData['agent']['source_type'] = $tenant->slug;
                    $agent                             = StoreAgent::run(
                        owner: $tenant,
                        modelData: $agentData['agent'],
                        addressData: $agentData['address']
                    );

                    $tenant->agents()->updateExistingPivot($agent, ['source_id' => $agentData['agent']['source_id']]);
                }
            }

            return $agent;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Agent Dimension')
            ->select('Agent Key as source_id')
            ->where('aiku_ignore', 'No')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')
            ->table('Agent Dimension')
            ->select('Agent Key as source_id')
            ->where('aiku_ignore', 'No')
            ->count();
    }




}
