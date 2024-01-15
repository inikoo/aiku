<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\Helpers\Uploads\ImportUpload;
use App\Actions\Helpers\Uploads\StoreUploads;
use App\Actions\Traits\WithImportModel;
use App\Imports\CRM\CustomerImport;
use App\Models\CRM\Customer;
use App\Models\Helpers\Upload;

class ImportCustomers
{
    use WithImportModel;

    public function handle($file): Upload
    {
        $upload = StoreUploads::run($file, Customer::class);

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new CustomerImport($upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new CustomerImport($upload)
            );
        }

        return $upload;
    }

    public string $commandSignature = 'customer:import {--g|g_drive} {filename}';

}
