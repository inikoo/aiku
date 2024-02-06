<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Helpers\Uploads\ImportUpload;
use App\Actions\Helpers\Uploads\StoreUploads;
use App\Actions\Traits\WithImportModel;
use App\Http\Resources\Helpers\UploadsResource;
use App\Imports\CRM\PalletImport;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportPallet
{
    use WithImportModel;

    public function handle(PalletDelivery $palletDelivery, $file): Upload
    {
        $upload = StoreUploads::run($file, Pallet::class);

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new PalletImport($palletDelivery, $upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new PalletImport($palletDelivery, $upload)
            );
        }

        return $upload;
    }

    public function inPalletDelivery(PalletDelivery $palletDelivery, ActionRequest $request): Upload
    {
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($palletDelivery, $file);
    }

    public function jsonResponse(Upload $upload): array
    {
        return UploadsResource::make($upload)->getArray();
    }

    public function rumImport($file, $command): Upload
    {
        $palletDelivery = PalletDelivery::where('slug', $command->argument('palletDelivery'))->first();

        return $this->handle($palletDelivery, $file);
    }

    public string $commandSignature = 'pallet:import {palletDelivery} {--g|g_drive} {filename}';
}
