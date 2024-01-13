<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:08:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\CRM\Prospect\StoreProspect;
use App\Actions\CRM\Prospect\UpdateProspect;
use App\Models\CRM\Prospect;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchProspects extends FetchAction
{
    public string $commandSignature = 'fetch:prospects {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--w|with=* : Accepted values: clients orders web-users} {--N|only_new : Fetch only new} {--d|db_suffix=}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Prospect
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

            } else {
                $prospect = StoreProspect::make()->action(
                    $prospectData['shop'],
                    $prospectData['prospect'],
                    60,
                    false
                );
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
