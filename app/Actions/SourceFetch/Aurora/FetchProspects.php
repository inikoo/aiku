<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:08:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Leads\Prospect\StoreProspect;
use App\Actions\Leads\Prospect\UpdateProspect;
use App\Models\Leads\Prospect;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchProspects extends FetchAction
{
    public string $commandSignature = 'fetch:prospects {tenants?*} {--s|source_id=} {--S|shop= : Shop slug} {--w|with=* : Accepted values: clients orders web-users} {--N|only_new : Fetch only new}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Prospect
    {
        if ($prospectData = $tenantSource->fetchProspect($tenantSourceId)) {
            if ($prospect = Prospect::withTrashed()->where('source_id', $prospectData['prospect']['source_id'])
                ->first()) {
                $prospect = UpdateProspect::run($prospect, $prospectData['prospect']);

                UpdateAddress::run($prospect->getAddress('contact'), $prospectData['contact_address']);
                $prospect->location = $prospect->getLocation();
                $prospect->save();
            } else {
                $prospect = StoreProspect::run($prospectData['shop'], $prospectData['prospect'], $prospectData['contact_address']);
            }


            DB::connection('aurora')->table('Prospect Dimension')
                ->where('Prospect Key', $prospect->source_id)
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
