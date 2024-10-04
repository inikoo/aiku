<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 13:06:26 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Helpers\Upload\StoreUpload;
use App\Actions\Helpers\Upload\UpdateUpload;
use App\Models\Helpers\Upload;
use App\Models\HumanResources\ClockingMachine;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraUploads extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:uploads {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?ClockingMachine
    {
        if ($uploadData = $organisationSource->fetchClockingMachine($organisationSourceId)) {

            if ($upload = Upload::where('source_id', $uploadData['upload']['source_id'])->first()) {
                $upload = UpdateUpload::make()->action(
                    clockingMachine: $upload,
                    modelData: $uploadData['upload']
                );
            } else {
                $upload = StoreUpload::make()->action(
                    workplace: $uploadData['workplace'],
                    modelData: $uploadData['upload'],
                );


            }

            return $upload;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Upload Dimension')
            ->select('Upload Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Upload Dimension')->count();
    }


}
