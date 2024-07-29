<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 01:34:40 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDeliveryStateFromItems;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\Rental;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class SetPalletRental extends OrgAction
{
    use WithActionUpdate;

    private Pallet $pallet;

    public function handle(Pallet $pallet, array $modelData): Pallet
    {
        $rental = Rental::find($modelData['rental_id']);
        $rentalAgreementClauses = $pallet->fulfilmentCustomer->rentalAgreementClauses;
        foreach ($rentalAgreementClauses as $clause) {
            if ($clause->asset_id === $rental->asset_id) {
                data_set($modelData, 'rental_agreement_clause_id', $clause->id);
                break;
            }
        }
        $pallet             = $this->update($pallet, $modelData);
        UpdatePalletDeliveryStateFromItems::run($pallet->palletDelivery);
        return $pallet;
    }

    public function rules(): array
    {
        return [
            'rental_id' => [
                'required',
                Rule::exists('rentals', 'id')
                    ->where('shop_id', $this->fulfilment->shop_id),
            ],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function asController(Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisationFromFulfilment($pallet->fulfilment, $request);
        return $this->handle($pallet, $this->validatedData);
    }

    public function action(Pallet $pallet, array $modelData): Pallet
    {
        $this->asAction       = true;
        $this->initialisationFromFulfilment($pallet->fulfilment, $modelData);
        return $this->handle($pallet, $this->validatedData);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
