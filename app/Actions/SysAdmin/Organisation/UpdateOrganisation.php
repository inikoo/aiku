<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Helpers\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\Media\SaveModelImage;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\SysAdmin\Organisation;
use App\Rules\ValidAddress;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\File;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrganisation extends OrgAction
{
    use WithActionUpdate;


    public function handle(Organisation $organisation, array $modelData): Organisation
    {
        $processedModelData = [];
        foreach ($modelData as $key => $value) {
            data_set(
                $processedModelData,
                match ($key) {
                    'ui_name' => 'settings.ui.name',
                    'google_client_id' => 'settings.google.id',
                    'google_client_secret' => 'settings.google.secret',
                    'google_drive_folder_key' => 'settings.google.drive.folder',
                    default => $key
                },
                $value
            );
        }


        if (Arr::has($modelData, 'address')) {
            $addressData = Arr::get($modelData, 'address');
            Arr::forget($modelData, 'address');
            UpdateAddress::run($organisation->address, $addressData);
            $organisation->updateQuietly(
                [
                    'location' => $organisation->address->getLocation()
                ]
            );
        }

        if (Arr::has($modelData, 'logo')) {
            /** @var UploadedFile $image */
            $image = Arr::get($modelData, 'logo');
            data_forget($modelData, 'logo');
            $imageData    = [
                'path'         => $image->getPathName(),
                'originalName' => $image->getClientOriginalName(),
                'extension'    => $image->getClientOriginalExtension(),
            ];
            $organisation = SaveModelImage::run(
                model: $organisation,
                imageData: $imageData,
                scope: 'logo'
            );
        }

        $organisation = $this->update($organisation, $modelData, ['data', 'settings']);

        if ($organisation->wasChanged('created_at')) {
            SetCurrencyHistoricFields::run($organisation->currency, $organisation->created_at);
        }

        return $organisation;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo(
            [
                'organisations.edit',
                'org-admin.'.$this->organisation->id
            ]
        );
    }

    public function rules(): array
    {
        $rules = [
            'name'                    => ['sometimes', 'required', 'string', 'max:255'],
            'ui_name'                 => ['sometimes', 'required', 'string', 'max:24'],
            'contact_name'            => ['sometimes', 'string', 'max:255'],
            'google_client_id'        => ['sometimes', 'string'],
            'google_client_secret'    => ['sometimes', 'string'],
            'google_drive_folder_key' => ['sometimes', 'string'],
            'address'                 => ['sometimes', 'required', new ValidAddress()],
            'language_id'             => ['sometimes', 'exists:languages,id'],
            'timezone_id'             => ['sometimes', 'exists:timezones,id'],
            'currency_id'             => ['sometimes', 'exists:currencies,id'],
            'logo'                    => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(12 * 1024)
            ]
        ];

        if (!$this->strict) {
            $rules['last_fetched_at'] = ['sometimes', 'date'];
            $rules['source']          = ['sometimes', 'array'];
            $rules['created_at']      = ['sometimes', 'date'];
            $rules['source_id']       = ['sometimes', 'string'];
        }

        return $rules;
    }


    public function asController(Organisation $organisation, ActionRequest $request): Organisation
    {
        $this->initialisation($organisation, $request);

        return $this->handle(
            organisation: $organisation,
            modelData: $this->validatedData
        );
    }

    public function action(Organisation $organisation, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Organisation
    {
        $this->strict = $strict;
        if (!$audit) {
            Organisation::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }
}
