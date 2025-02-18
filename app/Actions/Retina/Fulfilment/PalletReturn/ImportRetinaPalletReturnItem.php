<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Helpers\Upload\ImportUpload;
use App\Actions\Helpers\Upload\StoreUpload;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithImportModel;
use App\Http\Resources\Helpers\UploadsResource;
use App\Imports\Fulfilment\PalletReturnItemImport;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Upload;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportRetinaPalletReturnItem extends RetinaAction
{
    use WithImportModel;

    public function handle(PalletReturn $palletReturn, $file): Upload
    {
        $upload = StoreUpload::make()->fromFile(
            $palletReturn->organisation,
            $file,
            [
                'model' => 'PalletReturnItem',
                'parent_type' => class_basename($palletReturn),
                'parent_id' => $palletReturn->id,
                'customer_id' => $palletReturn->fulfilmentCustomer->customer_id,
            ]
        );

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new PalletReturnItemImport($palletReturn, $upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new PalletReturnItemImport($palletReturn, $upload)
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

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): Upload
    {
        $request->validate();
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($palletReturn, $file, $request->input('with_stored_item'));
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
}
