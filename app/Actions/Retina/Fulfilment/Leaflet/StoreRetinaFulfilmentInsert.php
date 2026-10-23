<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\Leaflet;

use App\Actions\Helpers\Media\StoreMediaFromFile;
use App\Actions\RetinaAction;
use App\Enums\Catalogue\Leaflet\LeafletStateEnum;
use App\Models\Billables\ModelHasLeaflet;
use App\Models\CRM\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreRetinaFulfilmentInsert extends RetinaAction
{
    use WithRetinaFulfilmentInserts;

    public function handle(Customer $customer, array $modelData): ModelHasLeaflet
    {
        /** @var UploadedFile $file */
        $file = Arr::get($modelData, 'file');

        $media = StoreMediaFromFile::run(
            $customer,
            [
                'path'         => $file->getPathName(),
                'originalName' => $file->getClientOriginalName(),
                'extension'    => $file->getClientOriginalExtension(),
                'checksum'     => md5_file($file->getPathName()),
            ],
            'leaflets',
            'leaflet'
        );

        return ModelHasLeaflet::create([
            'group_id'        => $customer->group_id,
            'organisation_id' => $customer->organisation_id,
            'shop_id'         => $customer->shop_id,
            'model_type'      => 'Customer',
            'model_id'        => $customer->id,
            'type'            => Arr::get($modelData, 'type'),
            'name'            => Arr::get($modelData, 'name'),
            'media_id'        => $media->id,
            'state'           => Arr::get($modelData, 'included', true) ? LeafletStateEnum::ACTIVE : LeafletStateEnum::INACTIVE,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'type'     => ['required', ...$this->insertTypeRules()],
            'file'     => ['required', ...$this->insertFileRules()],
            'included' => ['sometimes', 'boolean'],
        ];
    }

    public function asController(ActionRequest $request): ModelHasLeaflet
    {
        $this->initialisation($request);

        return $this->handle($this->customer, $this->validatedData);
    }

    public function htmlResponse(ModelHasLeaflet $customerLeaflet): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => __('Insert :name uploaded.', ['name' => $customerLeaflet->name]),
        ]);
    }
}
