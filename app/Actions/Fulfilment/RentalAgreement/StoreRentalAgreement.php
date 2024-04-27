<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreement;

use App\Actions\Fulfilment\Rental\UpdateRental;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Rental;
use App\Models\Fulfilment\RentalAgreement;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

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



        foreach (Arr::get($modelData, 'rental', []) as $rental) {
            data_set($rental, 'rental_id', Arr::get($rental, 'rental'));
            data_set($rental, 'agreed_price', Arr::get($rental, 'agreed_price'));

            $fulfilmentCustomer->rentalAgreementClauses()->create(Arr::only($rental, ['rental_id', 'agreed_price']));

            UpdateRental::run(Rental::find(Arr::get($rental, 'rental')), [
                'main_outerable_price' => Arr::get($rental, 'price')
            ]);
        }

        data_forget($modelData, 'rental');

        /** @var RentalAgreement $rentalAgreement */
        $rentalAgreement=$fulfilmentCustomer->rentalAgreement()->create($modelData);

        $fulfilmentCustomer->update(
            [
                'rental_agreement_state'=> $rentalAgreement->state
            ]
        );


        return $rentalAgreement;
    }

    public function rules(): array
    {
        return [
            'billing_cycle'             => ['required', Rule::enum(RentalAgreementBillingCycleEnum::class)],
            'pallets_limit'             => ['nullable','integer','min:1','max:10000'],
            'rental'                    => ['sometimes','nullable', 'array'],
            'rental.*.rental'           => ['required', 'exists:rentals,id'],
            'rental.*.agreed_price'     => ['required', 'numeric', 'gt:0'],
            'rental.*.price'            => ['required', 'numeric', 'gt:0'],
            'state'                     => ['sometimes',Rule::enum(RentalAgreementStateEnum::class)],
            'created_at'                => ['sometimes','date'],
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

    public function htmlResponse(RentalAgreement $rentalAgreement): Response
    {
        return Inertia::location(route('grp.org.fulfilments.show.crm.customers.show', [
            'organisation'       => $rentalAgreement->organisation->slug,
            'fulfilment'         => $rentalAgreement->fulfilment->slug,
            'fulfilmentCustomer' => $rentalAgreement->fulfilmentCustomer->slug
        ]));
    }
}
