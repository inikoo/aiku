<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 19 Jan 2024 12:04:12 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Actions\Helpers\Uploads\ImportUpload;
use App\Actions\Helpers\Uploads\StoreUploads;
use App\Actions\Traits\WithImportModel;
use App\Imports\Auth\GuestImport;
use App\Imports\Location\LocationImport;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Location;
use App\Models\SysAdmin\Guest;

class ImportLocation
{
    use WithImportModel;

    public function handle($file): Upload
    {
        $upload = StoreUploads::run($file, Location::class);

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new LocationImport($upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new LocationImport($upload)
            );
        }

        return $upload;


    }

    public string $commandSignature = 'location:import {--g|g_drive} {filename}';
}
