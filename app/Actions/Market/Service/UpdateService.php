<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 14:19:24 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Service;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Market\Service\ServiceStateEnum;
use App\Models\Market\Service;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateService extends OrgAction
{
    use WithActionUpdate;


    public function handle(Service $service, array $modelData): Service
    {
        return $this->update($service, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
          'status'=> ['sometimes','required','boolean'],
          'state' => ['sometimes','required',Rule::enum(ServiceStateEnum::class)],

        ];
    }

    public function asController(Service $service, ActionRequest $request): Service
    {
        $this->initialisationFromShop($service->shop, $request);
        return $this->handle($service, $this->validatedData);
    }

    public function action(Service $service, array $modelData, int $hydratorsDelay = 0): Service
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($service->shop, $modelData);
        return $this->handle($service, $this->validatedData);
    }


}
