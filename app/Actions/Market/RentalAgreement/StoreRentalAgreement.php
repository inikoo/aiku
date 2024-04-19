<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 23:40:14 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\RentalAgreement;

use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Market\RentalAgreement;
use Illuminate\Support\Arr;

class StoreRentalAgreement extends OrgAction
{
    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): RentalAgreement
    {
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'fulfilment_id', $fulfilmentCustomer->fulfilment_id);

        data_set(
            $modelData,
            'reference',
            GetSerialReference::run(
                container: $fulfilmentCustomer->fulfilment,
                modelType: SerialReferenceModelEnum::RENTAL_AGREEMENT
            )
        );

        foreach (Arr::get($modelData, 'rental') as $rental) {
            $price = Arr::get($rental, 'price');
            $disc  = Arr::get($rental, 'disc');

            $agreedPrice = $price - ($price * ($disc / 100));

            $fulfilmentCustomer->rentalAgreementClauses()->create([
                'rental_id'    => Arr::get($rental, 'rental_id'),
                'agreed_price' => $agreedPrice
            ]);
        }

        /** @var RentalAgreement $rentalAgreement */
        $rentalAgreement=$fulfilmentCustomer->rentalAgreement()->create($modelData);

        return $rentalAgreement;
    }

    public function rules(): array
    {
        return [
            'billing_cycle'      => ['required','nullable','integer','min:1','max:100'],
            'pallets_limit'      => ['required','nullable','integer','min:1','max:10000'],
            'rental'             => ['required', 'array'],
            'rental.*.rental_id' => ['required', 'exists:rentals,id'],
            'rental.*.price'     => ['required', 'string'],
            'rental.*.disc'      => ['required', 'string'],
        ];
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData): RentalAgreement
    {
        $this->asAction       = true;

        $this->initialisationFromShop($fulfilmentCustomer->fulfilment->shop, $modelData);
        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }




}
