<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\RawMaterial;

use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Helpers\Uploads\ImportUpload;
use App\Actions\Helpers\Uploads\StoreUploads;
use App\Actions\OrgAction;
use App\Actions\Traits\WithImportModel;
use App\Http\Resources\Helpers\UploadsResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Warehouse;
use App\Models\Manufacturing\Production;
use App\Models\Manufacturing\RawMaterial;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportRawMaterial extends OrgAction
{
    use WithImportModel;



    public function handle(Production $production, $file): Upload
    {
        $upload = StoreUploads::run($file, RawMaterial::class);

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new RawMaterialImport($production, $upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new RawMaterialImport($production, $upload)
            );
        }

        return $upload;
    }

    public function authorize(ActionRequest $request): bool
    {

        return true;
    }

    public function asController(Production $production, ActionRequest $request): Upload
    {
        $request->validate();
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);
        return $this->handle($production, $file);
    }

    public function jsonResponse(Upload $upload): array
    {
        return UploadsResource::make($upload)->getArray();
    }

    // public function rumImport($file, $command): Upload
    // {
    //     if ($palletDeliverySlug = $command->argument('palletDelivery')) {
    //         $palletDelivery = PalletDelivery::where('slug', $palletDeliverySlug)->first();
    //     } else {
    //         $warehouse          = Warehouse::where('slug', $command->argument('warehouse'))->first();
    //         $fulfilmentCustomer = FulfilmentCustomer::where('slug', $command->argument('fulfilmentCustomer'))->first();
    //         $palletDelivery     = StorePalletDelivery::run($fulfilmentCustomer, [
    //             'warehouse_id' => $warehouse->id,
    //         ]);
    //     }

    //     return $this->handle($palletDelivery, $file);
    // }

    // public string $commandSignature = 'pallet:import {--g|g_drive} {filename} {fulfilmentCustomer?} {warehouse?} {palletDelivery?}';
}
