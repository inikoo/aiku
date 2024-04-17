<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 14:19:24 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Rental;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Market\Rental\RentalStateEnum;
use App\Models\Market\Rental;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateRental extends OrgAction
{
    use WithActionUpdate;


    public function handle(Rental $rental, array $modelData): Rental
    {
        return $this->update($rental, $modelData);
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
          'state' => ['sometimes','required',Rule::enum(RentalStateEnum::class)],

        ];
    }

    public function asController(Rental $rental, ActionRequest $request): Rental
    {
        $this->initialisationFromShop($rental->shop, $request);
        return $this->handle($rental, $this->validatedData);
    }

    public function action(Rental $rental, array $modelData, int $hydratorsDelay = 0): Rental
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($rental->shop, $modelData);
        return $this->handle($rental, $this->validatedData);
    }


}
