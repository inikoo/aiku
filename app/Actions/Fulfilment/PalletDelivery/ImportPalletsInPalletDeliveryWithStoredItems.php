<?php

/*
 * author Arya Permana - Kirin
 * created on 13-02-2025-09h-57m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Helpers\Upload\ImportUpload;
use App\Actions\Helpers\Upload\StoreUpload;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithFulfilmentAuthorisation;
use App\Actions\Traits\WithImportModel;
use App\Imports\Fulfilment\PalletImportWithStoredItems;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportPalletsInPalletDeliveryWithStoredItems extends OrgAction
{
    use WithImportModel;
    use WithFulfilmentAuthorisation;

    private Fulfilment $parent;
    public function handle(PalletDelivery $palletDelivery, $file, array $modelData): Upload
    {
        $upload = StoreUpload::make()->fromFile(
            $palletDelivery->fulfilment->shop,
            $file,
            [
                'model' => 'Pallet',
                'customer_id' => $palletDelivery->fulfilmentCustomer->customer_id,
                'parent_type' => $palletDelivery->getMorphClass(),
                'parent_id' => $palletDelivery->id,
            ]
        );

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new PalletImportWithStoredItems($palletDelivery, $upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new PalletImportWithStoredItems($palletDelivery, $upload)
            );
        }

        return $upload;
    }

    public function rules(): array
    {
        return [
            'file'             => ['required', 'file', 'mimes:xlsx,csv,xls,txt'],
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

    public function runImportForCommand($file, $command): Upload
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

    public string $commandSignature = 'pallet_with_stored_items:import {--g|g_drive} {filename} {fulfilmentCustomer?} {warehouse?} {palletDelivery?}';
}
