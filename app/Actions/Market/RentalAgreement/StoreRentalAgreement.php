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
use Lorisleiva\Actions\ActionRequest;

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
            data_set($rental, 'rental', Arr::get($rental, 'rental'));
            data_set($rental, 'agreed_price', Arr::get($rental, 'agreed_price'));

            $fulfilmentCustomer->rentalAgreementClauses()->create($rental);
        }

        data_forget($modelData, 'rental');

        /** @var RentalAgreement $rentalAgreement */
        $rentalAgreement=$fulfilmentCustomer->rentalAgreement()->create($modelData);

        return $rentalAgreement;
    }

    public function rules(): array
    {
        return [
            'billing_cycle'             => ['required','integer','min:1','max:100'],
            'pallets_limit'             => ['nullable','integer','min:1','max:10000'],
            'rental'                    => ['required', 'array'],
            'rental.*.rental'           => ['required', 'exists:rentals,id'],
            'rental.*.agreed_price'     => ['required', 'numeric', 'gt:0'],
            'rental.*.price'            => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData): RentalAgreement
    {
        $this->asAction       = true;

        $this->initialisationFromShop($fulfilmentCustomer->fulfilment->shop, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): RentalAgreement
    {
        $this->initialisationFromShop($fulfilmentCustomer->fulfilment->shop, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }
}
