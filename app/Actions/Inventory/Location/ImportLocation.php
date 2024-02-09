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
use App\Imports\Location\LocationImport;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportLocation
{
    use WithImportModel;

    public function handle(Warehouse|WarehouseArea|Organisation $parent, $file): Upload
    {
        $upload = StoreUploads::run($file, Location::class);

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new LocationImport($parent, $upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new LocationImport($parent, $upload)
            );
        }

        return $upload;


    }

    public function rumImport($file, $command): Upload
    {
        $warehouse     = Warehouse::where('slug', $command->argument('warehouse'))->first();
        $warehouseArea = WarehouseArea::where('slug', $command->argument('warehouse'))->first();

        if(!$warehouse && !$warehouseArea) {
            throw new \Exception('Warehouse or Warehouse Area not found');
        }

        if($warehouse) {
            return $this->handle($warehouse, $file);
        } else {
            return $this->handle($warehouseArea, $file);
        }
    }

    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): Upload
    {
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($warehouse, $file);
    }

    public function inWarehouseArea(Organisation $organisation, Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): Upload
    {
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($warehouseArea, $file);
    }

    public function asController(Organisation $organisation, ActionRequest $request): Upload
    {
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($organisation, $file);
    }

    public string $commandSignature = 'location:import {warehouse} {--g|g_drive} {filename}';
}
