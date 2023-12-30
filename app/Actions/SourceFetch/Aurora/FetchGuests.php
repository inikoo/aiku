<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 14:05:59 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Actions\SysAdmin\Guest\UpdateGuest;
use App\Actions\Utils\StoreImage;
use App\Models\SysAdmin\Guest;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchGuests extends FetchAction
{
    public string $commandSignature = 'fetch:guests {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Guest
    {
        if ($guestData = $organisationSource->fetchGuest($organisationSourceId)) {


            if ($guest = Guest::where('source_id', $guestData['guest']['source_id'])->first()) {
                $guest = UpdateGuest::make()->action(
                    guest:     $guest,
                    modelData: Arr::except($guestData['guest'], ['source_id','username','password'])
                );
            } else {
                $guest = StoreGuest::make()->action(
                    group:     $organisationSource->getOrganisation()->group,
                    modelData: $guestData['guest'],
                );

                $guest->user->updateQuietly(
                    [
                        'source_id' => $guestData['user']['source_id'],
                    ]
                );

            }


            foreach ($guestData['photo'] ?? [] as $profileImage) {
                if (isset($profileImage['image_path']) and isset($profileImage['filename'])) {
                    StoreImage::run($guest, $profileImage['image_path'], $profileImage['filename']);
                }
            }


            return $guest;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Staff Dimension')
            ->select('Staff Key as source_id')
            ->where('Staff Currently Working', 'Yes')
            ->where('Staff Type', '=', 'Contractor')
            ->where(function ($query) {
                $query->whereNull('aiku_ignore')
                    ->orWhere('aiku_ignore', 'No');
            })
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Staff Dimension')
            ->where('Staff Currently Working', 'Yes')
            ->where('Staff Type', '=', 'Contractor')
            ->where(function ($query) {
                $query->whereNull('aiku_ignore')
                    ->orWhere('aiku_ignore', 'No');
            })
            ->count();
    }
}
