<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:08:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\CRM\Prospect\StoreProspect;
use App\Actions\CRM\Prospect\UpdateProspect;
use App\Actions\Helpers\Fetch\UpdateFetch;
use App\Models\CRM\Prospect;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchProspects extends FetchAction
{
    public string $commandSignature = 'fetch:prospects {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--w|with=* : Accepted values: clients orders web-users} {--N|only_new : Fetch only new} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Prospect
    {
        if ($prospectData = $organisationSource->fetchProspect($organisationSourceId)) {
            if ($prospect = Prospect::withTrashed()->where('source_id', $prospectData['prospect']['source_id'])
                ->first()) {
                $prospect = UpdateProspect::make()->action(
                    $prospect,
                    $prospectData['prospect'],
                    60,
                    false
                );


                if ($prospect->wasChanged()) {
                    $this->number_updates++;
                    UpdateFetch::run($this->fetch, ['number_updates' => $this->number_updates]);
                } else {
                    $this->number_no_changes++;
                    UpdateFetch::run($this->fetch, ['number_no_changes' => $this->number_no_changes]);
                }
            } else {
                //print_r($prospectData['prospect']);
                $prospect = StoreProspect::make()->action(
                    $prospectData['shop'],
                    $prospectData['prospect'],
                    60,
                    false
                );
                $this->number_stores++;
                UpdateFetch::run($this->fetch, ['number_stores' => $this->number_stores]);
            }


            $sourceData = explode(':', $prospect->source_id);
            DB::connection('aurora')->table('Prospect Dimension')
                ->where('Prospect Key', $sourceData[1])
                ->update(['aiku_id' => $prospect->id]);

            return $prospect;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Prospect Dimension')
            ->select('Prospect Key as source_id')
            ->orderBy('source_id');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $query->where('Prospect Store Key', $this->shop->source_id);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Prospect Dimension');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        if ($this->shop) {
            $query->where('Prospect Store Key', $this->shop->source_id);
        }

        return $query->count();
    }
}
