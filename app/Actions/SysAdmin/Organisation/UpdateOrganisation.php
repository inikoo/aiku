<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Traits\WithActionUpdate;
use App\Models\SysAdmin\Organisation;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrganisation
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Organisation $organisation, array $modelData): Organisation
    {
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

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function rules(): array
    {
        return [
            'name'                    => ['sometimes', 'required', 'max:24', 'string'],
            'google_client_id'        => ['sometimes', 'string'],
            'google_client_secret'    => ['sometimes', 'string'],
            'google_drive_folder_key' => ['sometimes', 'string'],
            'address'                 => ['sometimes', 'required', new ValidAddress()],
            'created_at'              => ['sometimes', 'date'],
            'language_id'             => ['sometimes', 'exists:languages,id'],
            'timezone_id'             => ['sometimes', 'exists:timezones,id'],
            'currency_id'             => ['sometimes', 'exists:currencies,id'],
            'source'                  => ['sometimes', 'array'],
        ];
    }


    public function asController(Organisation $organisation, ActionRequest $request): Organisation
    {
        $this->fillFromRequest($request);

        $modelData = [];
        foreach ($this->validateAttributes() as $key => $value) {
            data_set(
                $modelData,
                match ($key) {
                    'name'                    => 'settings.ui.name',
                    'google_client_id'        => 'settings.google.id',
                    'google_client_secret'    => 'settings.google.secret',
                    'google_drive_folder_key' => 'settings.google.drive.folder',
                    default                   => $key
                },
                $value
            );
        }

        return $this->handle(
            organisation: $organisation,
            modelData: $modelData
        );
    }

    public function action(Organisation $organisation, $modelData): Organisation
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($organisation, $validatedData);
    }
}
