<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\PalletReturn\AutoAssignServicesToPalletReturn;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsCommand;

class AttachPalletToReturnFromImport extends OrgAction
{
    use AsCommand;

    private PalletReturn $parent;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $reference  = Arr::get($modelData, 'reference');
        $pallet     = Pallet::where('reference', $reference)
                    ->where('fulfilment_customer_id', $palletReturn->fulfilment_customer_id)
                    ->first();

        $this->attach($palletReturn, $pallet);

        $palletReturn->refresh();

        PalletReturnHydratePallets::run($palletReturn);

        return $palletReturn;
    }

    private function attach(PalletReturn $palletReturn, Pallet $pallet): void
    {
        $palletReturn->pallets()->attach($pallet->id, [
            'quantity_ordered'     => 1,
            'type'                 => 'Pallet'
        ]);

        $pallet = UpdatePallet::make()->action($pallet, [
            'pallet_return_id' => $palletReturn->id,
            'status' => PalletStatusEnum::RETURNING,
            'state'  => PalletStateEnum::REQUEST_RETURN_IN_PROCESS,
            'requested_for_return_at' => now()
        ]);


        AutoAssignServicesToPalletReturn::run($palletReturn, $pallet);
    }

    public function rules(): array
    {
        return [
            'reference' => [
                'required',
                'string',
                Rule::exists('pallets', 'reference')->where(function ($query) {
                    $query->where('fulfilment_customer_id', $this->parent->fulfilment_customer_id)
                    ->where('status', PalletStatusEnum::STORING);
                })
            ],
        ];
    }

    public function action(PalletReturn $palletReturn, array $modelData, int $hydratorsDelay = 0): PalletReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $palletReturn;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function prepareForValidation()
    {
        $reference = $this->get('reference');


        $pallet = Pallet::where('reference', $reference)
                        ->where('fulfilment_customer_id', $this->parent->fulfilment_customer_id)
                        ->first();

        if ($pallet && $this->parent->pallets()->where('pallet_id', $pallet->id)->exists()) {
            throw ValidationException::withMessages([
                'reference' => ['This pallet is already attached to the pallet return.'],
            ]);
        }
    }
}
