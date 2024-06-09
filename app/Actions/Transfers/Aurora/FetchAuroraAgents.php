<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 08:37:56 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Helpers\Media\SaveModelImage;
use App\Actions\Procurement\OrgAgent\StoreOrgAgent;
use App\Actions\SupplyChain\Agent\StoreAgent;
use App\Actions\SupplyChain\Agent\UpdateAgent;
use App\Models\Procurement\OrgAgent;
use App\Models\SupplyChain\Agent;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchAuroraAgents extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:agents {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Agent
    {
        setPermissionsTeamId($organisationSource->getOrganisation()->group_id);

        if ($agentData = $organisationSource->fetchAgent($organisationSourceId)) {
            $organisation = $organisationSource->getOrganisation();

            $baseAgent = null;

            if (isset($agentData['foundAgent'])) {
                $agent = $agentData['foundAgent'];
            } elseif ($baseAgent = Agent::withTrashed()->where('source_slug', $agentData['agent']['source_slug'])->first()) {
                if ($agent = Agent::withTrashed()->where('source_id', $agentData['agent']['source_id'])->first()) {
                    $agent = UpdateAgent::make()->run($agent, $agentData['agent']);
                }
            } else {
                $agent = StoreAgent::make()->action(
                    group: $organisation->group,
                    modelData: $agentData['agent'],
                );
                $agent->refresh();
                foreach (Arr::get($agentData, 'photo', []) as $photoData) {
                    if (isset($photoData['image_path']) and isset($photoData['filename'])) {
                        SaveModelImage::run(
                            $agent,
                            [
                                'path'         => $photoData['image_path'],
                                'originalName' => $photoData['filename'],

                            ],
                            'photo'
                        );
                    }
                }

            }


            if ($agent) {
                $agent->refresh();
                $orgAgent = OrgAgent::where('organisation_id', $organisation->id)->where('agent_id', $agent->id)->first();
                if ($orgAgent) {
                    return $agent;
                }

                StoreOrgAgent::make()->action(
                    $organisation,
                    $agent,
                    [
                        'source_id' => $agentData['agent']['source_id'],
                    ]
                );





                $sourceData = explode(':', $agentData['agent']['source_id']);

                DB::connection('aurora')->table('Agent Dimension')
                    ->where('Agent Key', $sourceData[1])
                    ->update(['aiku_id' => $agent->id]);
            } elseif ($baseAgent) {
                $orgAgent = OrgAgent::where('organisation_id', $organisation->id)->where('agent_id', $baseAgent->id)->first();
                if ($orgAgent) {
                    return $agent;
                }

                StoreOrgAgent::make()->action(
                    $organisation,
                    $baseAgent,
                    [
                        'source_id' => $agentData['agent']['source_id'],
                    ]
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


}
