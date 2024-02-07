<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\Helpers\Uploads\ImportUpload;
use App\Actions\Helpers\Uploads\StoreUploads;
use App\Actions\Traits\WithImportModel;
use App\Imports\Warehouse\WarehouseAreaImport;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;

class ImportWarehouseArea
{
    use WithImportModel;

    public function handle(Warehouse $warehouse, $file): Upload
    {
        $upload = StoreUploads::run($file, WarehouseArea::class);

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new WarehouseAreaImport($warehouse, $upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new WarehouseAreaImport($warehouse, $upload)
            );
        }

        return $upload;
    }

    public function rumImport($file, $command): Upload
    {
        $warehouse = Warehouse::where('slug', $command->argument('warehouse'))->first();

        if(!$warehouse) {
            throw new \Exception('Warehouse not found');
        }

        return $this->handle($warehouse, $file);
    }

    public string $commandSignature = 'warehouse-area:import {warehouse} {--g|g_drive} {filename}';

}
