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
use App\Models\Tenancy\Tenant;
use App\Services\Tenant\SourceTenantService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAgents extends FetchAction
{
    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Agent
    {
        if ($agentData = $tenantSource->fetchAgent($tenantSourceId)) {
            $owner=app('currentTenant');

            if ($agent = Agent::withTrashed()->where('source_id', $agentData['agent']['source_id'])
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

    public string $commandSignature = 'fetch:agents {owner : tenant owner} {--s|source_id= : aurora agent id (owner)}';


    public function asCommand(Command $command): int
    {
        try {
            $owner=Tenant::where('slug', $command->argument('owner'))->firstOrFail();
        } catch (Exception) {
            $command->error('Invalid owner');
            return 1;
        }

        $owner->makeCurrent();

        try {
            $tenantSource = $this->getTenantSource($owner);
        } catch (Exception $exception) {
            $command->error($exception->getMessage());
            return 1;
        }
        $tenantSource->initialisation($owner);


        if ($command->option('source_id')) {
            $this->handle($tenantSource, $command->option('source_id'));
        } else {
            $this->fetchAll($tenantSource, $command);
        }

        return 0;

    }





}
