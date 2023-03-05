<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 19:02:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Actions\SysAdmin\Guest\UpdateGuest;
use App\Models\SysAdmin\Guest;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchDeletedGuests extends FetchAction
{
    public string $commandSignature = 'fetch:deleted-guests {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Guest
    {
        if ($deletedGuestData = $tenantSource->fetchDeletedGuest($tenantSourceId)) {
            if ($guest = Guest::withTrashed()->where('source_id', $deletedGuestData['guest']['source_id'])->first()) {
                $guest = UpdateGuest::run(
                    guest:     $guest,
                    modelData: $deletedGuestData['guest']
                );
            } else {
                $guest = StoreGuest::run(
                    modelData: $deletedGuestData['guest'],
                );
            }

            return $guest;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Staff Deleted Dimension')
            ->select('Staff Deleted Key as source_id')
            ->where('Staff Deleted Type', '=', 'Contractor')
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Staff Deleted Dimension')
            ->where('Staff Deleted Type', '=', 'Contractor')
            ->count();
    }
}
