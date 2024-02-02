<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Helpers\Uploads\ImportUpload;
use App\Actions\Helpers\Uploads\StoreUploads;
use App\Actions\Traits\WithImportModel;
use App\Imports\CRM\CustomerImport;
use App\Imports\CRM\PalletDeliveryImport;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;

class ImportPalletDelivery
{
    use WithImportModel;

    public function handle($file): Upload
    {
        $upload = StoreUploads::run($file, PalletDelivery::class);

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new PalletDeliveryImport($upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new PalletDeliveryImport($upload)
            );
        }

        return $upload;
    }

    public string $commandSignature = 'pallet-delivery:import {--g|g_drive} {filename}';

}
