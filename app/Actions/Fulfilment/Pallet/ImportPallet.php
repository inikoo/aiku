<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Helpers\Upload\ImportUpload;
use App\Actions\Helpers\Upload\StoreUpload;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Actions\Traits\WithImportModel;
use App\Http\Resources\Helpers\UploadsResource;
use App\Imports\Fulfilment\PalletImport;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportPallet extends OrgAction
{
    use WithImportModel;
    use HasFulfilmentAssetsAuthorisation;

    private Fulfilment $parent;


    public function handle(PalletDelivery $palletDelivery, $file, array $modelData): Upload
    {
        $includeStoredItem = Arr::pull($modelData, 'with_stored_item', false);

        $upload = StoreUpload::make()->fromFile(
            $palletDelivery->organisation,
            $file,
            [
                'model' => 'Pallet',
            ]
        );

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new PalletImport($palletDelivery, $upload, $includeStoredItem)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new PalletImport($palletDelivery, $upload, $includeStoredItem)
            );
        }

        return $upload;
    }

    public function rules(): array
    {
        return [
            'file'             => ['required', 'file', 'mimes:xlsx,csv,xls,txt'],
            'with_stored_item' => ['sometimes', 'required', 'bool']
        ];
    }


    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): Upload
    {
        $this->parent = $palletDelivery->fulfilment;
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($palletDelivery, $file, $this->validatedData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $request->merge([
            'with_stored_item' => $request->input('stored_item') == "true"
        ]);
    }

    public function jsonResponse(Upload $upload): array
    {
        return UploadsResource::make($upload)->getArray();
    }

    public function runImport($file, $command): Upload
    {
        if ($palletDeliverySlug = $command->argument('palletDelivery')) {
            $palletDelivery = PalletDelivery::where('slug', $palletDeliverySlug)->first();
        } else {
            $warehouse          = Warehouse::where('slug', $command->argument('warehouse'))->first();
            $fulfilmentCustomer = FulfilmentCustomer::where('slug', $command->argument('fulfilmentCustomer'))->first();
            $palletDelivery     = StorePalletDelivery::run($fulfilmentCustomer, [
                'warehouse_id' => $warehouse->id,
            ]);
        }

        return $this->handle($palletDelivery, $file, []);
    }

    public string $commandSignature = 'pallet:import {--g|g_drive} {filename} {fulfilmentCustomer?} {warehouse?} {palletDelivery?}';
}
