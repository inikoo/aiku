<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Helpers\Upload\ImportUpload;
use App\Actions\Helpers\Upload\StoreUpload;
use App\Actions\Traits\WithImportModel;
use App\Http\Resources\Helpers\UploadsResource;
use App\Imports\Fulfilment\PalletReturnItemImport;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Upload;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportPalletReturnItem
{
    use AsAction;
    use WithImportModel;

    private string $origin = 'grp';

    public function handle(PalletReturn $palletReturn, $file): PalletReturn
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

        $palletReturn->refresh();

        return $palletReturn;
    }

    public function htmlResponse(PalletReturn $palletReturn)
    {
        return Inertia::location(route('grp.org.fulfilments.show.crm.customers.show.pallet_returns.show', [
            'organisation'       => $palletReturn->organisation->slug,
            'fulfilment'         => $palletReturn->fulfilment->slug,
            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug,
            'palletReturn'       => $palletReturn->slug,
        ]));
    }

    public function rules(): array
    {
        return [
            'file'             => ['required', 'file', 'mimes:xlsx,csv,xls,txt'],
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

    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $request->validate();
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($palletReturn, $file, $request->input('with_stored_item'));
    }

    public function fromGrp(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
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
