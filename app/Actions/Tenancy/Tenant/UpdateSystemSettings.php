<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Jun 2023 13:38:26 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Actions\WithActionUpdate;
use Lorisleiva\Actions\ActionRequest;

class UpdateSystemSettings
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(array $modelData): void
    {
        $tenant = app('currentTenant');


        $this->update($tenant, $modelData, ['data', 'settings']);
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
            'name' => ['sometimes', 'required', 'max:24', 'string'],
        ];
    }


    public function asController(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $modelData = [];
        foreach ($this->validateAttributes() as $key => $value) {
            data_set(
                $modelData,
                match ($key) {
                    'name'  => 'settings.ui.name',
                    default => $key
                },
                $value
            );
        }

        $this->handle(
            modelData: $modelData
        );
    }

    public function action($objectData): void
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        $this->handle($validatedData);
    }


}
