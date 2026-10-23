<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\Leaflet;

use App\Actions\Helpers\Media\StoreMediaFromFile;
use App\Actions\Retina\SysAdmin\WithDisposableLeafletMedia;
use App\Actions\RetinaAction;
use App\Enums\Catalogue\Leaflet\LeafletStateEnum;
use App\Models\Billables\ModelHasLeaflet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaFulfilmentInsert extends RetinaAction
{
    use WithRetinaFulfilmentInserts;
    use WithDisposableLeafletMedia;

    public function handle(ModelHasLeaflet $customerLeaflet, array $modelData): ModelHasLeaflet
    {
        return DB::transaction(function () use ($customerLeaflet, $modelData) {
            $previousMediaId = null;

            if ($file = Arr::pull($modelData, 'file')) {
                /** @var UploadedFile $file */
                $media = StoreMediaFromFile::run(
                    $this->customer,
                    [
                        'path'         => $file->getPathName(),
                        'originalName' => $file->getClientOriginalName(),
                        'extension'    => $file->getClientOriginalExtension(),
                        'checksum'     => md5_file($file->getPathName()),
                    ],
                    'leaflets',
                    'leaflet'
                );

                $previousMediaId        = $customerLeaflet->media_id;
                $modelData['media_id'] = $media->id;
            }

            if (Arr::has($modelData, 'included')) {
                $modelData['state'] = Arr::pull($modelData, 'included') ? LeafletStateEnum::ACTIVE : LeafletStateEnum::INACTIVE;
            }

            $customerLeaflet->update($modelData);

            if ($previousMediaId !== $customerLeaflet->media_id) {
                $this->deleteLeafletMediaIfUnused($previousMediaId);
            }

            return $customerLeaflet;
        });
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'required', 'string', 'max:255'],
            'type'     => ['sometimes', 'required', ...$this->insertTypeRules()],
            'file'     => ['sometimes', 'required', ...$this->insertFileRules()],
            'included' => ['sometimes', 'boolean'],
        ];
    }

    public function asController(ModelHasLeaflet $customerLeaflet, ActionRequest $request): ModelHasLeaflet
    {
        $this->initialisation($request);

        return $this->handle($customerLeaflet, $this->validatedData);
    }

    public function htmlResponse(ModelHasLeaflet $customerLeaflet): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => __('Insert :name updated.', ['name' => $customerLeaflet->name]),
        ]);
    }
}
