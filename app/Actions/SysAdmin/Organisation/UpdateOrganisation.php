<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\Traits\WithActionUpdate;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrganisation
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Organisation $organisation, array $modelData): Organisation
    {
        return $this->update($organisation, $modelData, ['data', 'settings']);
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
            'google_drive_folder_key' => ['sometimes', 'string']
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
