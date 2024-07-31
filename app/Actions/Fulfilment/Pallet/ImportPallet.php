<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Helpers\Uploads\ImportUpload;
use App\Actions\Helpers\Uploads\StoreUploads;
use App\Actions\Traits\WithImportModel;
use App\Http\Resources\Helpers\UploadsResource;
use App\Imports\CRM\PalletImport;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportPallet
{
    use WithImportModel;

    private string $origin = 'grp';

    public function handle(PalletDelivery $palletDelivery, $file, $includeStoredItem = false): Upload
    {
        $upload = StoreUploads::run($file, Pallet::class);

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
        $userable=$request->user();
        if($userable instanceof WebUser) {
            return true;
        }

        return true;
    }

    public function fromRetina(PalletDelivery $palletDelivery, ActionRequest $request): Upload
    {
        $request->validate();
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($palletDelivery, $file, $request->input('with_stored_item'));
    }

    public function fromGrp(PalletDelivery $palletDelivery, ActionRequest $request): Upload
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

    public string $commandSignature = 'pallet:import {--g|g_drive} {filename} {fulfilmentCustomer?} {warehouse?} {palletDelivery?}';
}
