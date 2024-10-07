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
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchAuroraUploads extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:uploads {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new} ';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Upload
    {
        if ($uploadData = $organisationSource->fetchUpload($organisationSourceId)) {
            if ($upload = Upload::where('source_id', $uploadData['upload']['source_id'])->first()) {
                $upload = UpdateUpload::make()->action(
                    upload: $upload,
                    modelData: $uploadData['upload'],
                    hydratorsDelay: 60,
                    strict: false,
                    audit: false
                );
            } else {
                $upload = StoreUpload::make()->action(
                    parent: $uploadData['parent'],
                    modelData: $uploadData['upload'],
                    hydratorsDelay: 60,
                    strict: false,
                    audit: false
                );

                Upload::enableAuditing();
                $this->saveMigrationHistory(
                    $upload,
                    Arr::except($uploadData['upload'], ['fetched_at', 'last_fetched_at'])
                );

                $sourceData = explode(':', $upload->source_id);
                DB::connection('aurora')->table('Upload Dimension')
                    ->where('Upload Key', $sourceData[1])
                    ->update(['aiku_id' => $upload->id]);
            }

            return $upload;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Upload Dimension')
            ->select('Upload Key as source_id');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->orderBy('Upload Created');
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Upload Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

}
