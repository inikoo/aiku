<?php

/*
 * author Arya Permana - Kirin
 * created on 16-01-2025-13h-23m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\Pallet;

use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Helpers\Upload\ImportUpload;
use App\Actions\Helpers\Upload\StoreUpload;
use App\Actions\Traits\WithImportModel;
use App\Http\Resources\Helpers\UploadsResource;
use App\Imports\Fulfilment\PalletImport;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class RetinaImportPallet
{
    use WithImportModel;

    private string $origin = 'grp';

    public function handle(PalletDelivery $palletDelivery, $file, $includeStoredItem = false): Upload
    {

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
            'with_stored_item' => ['required', 'bool']
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        $userable = $request->user();
        if ($userable instanceof WebUser) {
            return true;
        }

        return true;
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): Upload
    {
        $request->validate();
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($palletDelivery, $file, $request->input('with_stored_item'));
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

    public function rumImport($file, $command): Upload
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

        return $this->handle($palletDelivery, $file);
    }

    public string $commandSignature = 'retina-pallet:import {--g|g_drive} {filename} {fulfilmentCustomer?} {warehouse?} {palletDelivery?}';
}
