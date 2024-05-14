<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 11:16:30 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Manufacturing\Artifact\StoreArtifact;
use App\Actions\Manufacturing\Artifact\UpdateArtifact;
use App\Models\Manufacturing\Artifact;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraArtifacts extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:artifacts {organisations?*} {--s|source_id=} {--N|only_new : Fetch only new}  {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Artifact
    {
        $artifactData = $organisationSource->fetchArtifact($organisationSourceId);


        return $this->fetchArtifact($artifactData);
    }


    public function fetchArtifact($artifactData): ?Artifact
    {
        if ($artifactData) {
            if ($artifact = Artifact::withTrashed()->where('source_id', $artifactData['artifact']['source_id'])
                ->first()) {
                UpdateArtifact::make()->action(
                    artifact: $artifact,
                    modelData: $artifactData['artifact'],
                    hydratorDelay: $this->hydrateDelay
                );
            } else {
                $artifact = StoreArtifact::make()->action(
                    production: $artifactData['production'],
                    modelData: $artifactData['artifact'],
                    hydratorDelay: $this->hydrateDelay
                );

                $sourceData = explode(':', $artifact->source_id);
                DB::connection('aurora')->table('Supplier Part Dimension')
                    ->where('Supplier Part Key', $sourceData[1])
                    ->update(['aiku_id' => $artifact->id]);
            }


            return $artifact;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Supplier Part Dimension as spp')
            ->leftJoin('Part Dimension', 'Part SKU', 'Supplier Part Part SKU')
            ->leftJoin('Supplier Dimension as sd', 'Supplier Key', 'Supplier Part Supplier Key')
            ->select('Supplier Part Key as source_id');
        if ($this->onlyNew) {
            $query->whereNull('spp.aiku_id');
        }

        return $query->where('Supplier Part Status', ['Available', 'NoAvailable'])
            ->where('Part Status', '!=', 'Not In Use')
            ->where('spp.aiku_ignore', 'No')
            ->where('sd.Supplier Production', 'Yes')
            ->where('sd.Supplier Type', 'Free')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')
            ->table('Supplier Part Dimension as spp')
            ->leftJoin('Part Dimension', 'Part SKU', 'Supplier Part Part SKU')
            ->leftJoin('Supplier Dimension as sd', 'Supplier Key', 'Supplier Part Supplier Key')
            ->select('Supplier Part Key as source_id');
        if ($this->onlyNew) {
            $query->whereNull('spp.aiku_id');
        }

        return $query->where('Supplier Part Status', ['Available', 'NoAvailable'])
            ->where('Part Status', '!=', 'Not In Use')
            ->where('spp.aiku_ignore', 'No')
            ->where('sd.Supplier Production', 'Yes')
            ->where('sd.Supplier Type', 'Free')
            ->count();
    }
}
