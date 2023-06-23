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
        $tenant=app('currentTenant');
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
            'code' => ['sometimes', 'required', 'unique:tenant.warehouses', 'between:2,4', 'alpha'],
            'name' => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }


    public function asController(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->handle(
            modelData: $this->validateAttributes()
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
