<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 08:37:56 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Agent\UpdateAgent;
use App\Models\Procurement\Agent;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAgents extends FetchAction
{
    public string $commandSignature = 'fetch:agents {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Agent
    {
        if ($agentData = $organisationSource->fetchAgent($organisationSourceId)) {
            $organisation = $organisationSource->getOrganisation();


            if (Agent::withTrashed()->where('source_slug', $agentData['agent']['source_slug'])->exists()) {
                if ($agent = Agent::withTrashed()->where('source_id', $agentData['agent']['source_id'])->first()) {
                    $agent = UpdateAgent::make()->run($agent, $agentData['agent']);

                    // UpdateAddress::run($agent->getAddress('contact'), $agentData['address']);
                    // $agent->location = $agent->getLocation();
                    // $agent->save();
                }
            } else {
                $agentData['agent']['source_type'] = $organisation->slug;


                $agent = StoreAgent::make()->action(
                    group: $organisation->group,
                    modelData: $agentData['agent'],
                );
            }

            if ($agent) {
                foreach ($agentData['photo'] as $photoData) {
                    $this->saveImage($agent, $photoData);
                }


                $sourceData = explode(':', $agentData['agent']['source_id']);

                DB::connection('aurora')->table('Agent Dimension')
                    ->where('Agent Key', $sourceData[1])
                    ->update(['aiku_id' => $agent->id]);
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
