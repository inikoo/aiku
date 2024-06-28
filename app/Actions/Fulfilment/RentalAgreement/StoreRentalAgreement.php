<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreement;

use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\CRM\WebUser\UpdateWebUser;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStatus;
use App\Actions\Fulfilment\RentalAgreementClause\StoreRentalAgreementClause;
use App\Actions\Fulfilment\RentalAgreementSnapshot\StoreRentalAgreementSnapshot;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Fulfilment\RentalAgreementClause\RentalAgreementCauseStateEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RentalAgreement;
use App\Notifications\SendEmailRentalAgreementCreated;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

        $clauses = Arr::get($modelData, 'clauses', []);
        data_forget($modelData, 'clauses');




        /** @var RentalAgreement $rentalAgreement */
        $rentalAgreement = $fulfilmentCustomer->rentalAgreement()->create(Arr::except($modelData, ['username', 'is_root', 'email']));
        $rentalAgreement->stats()->create();
        $rentalAgreement->refresh();

        foreach ($clauses as $clauseData) {
            foreach ($clauseData as $data) {
                $data['state'] = match ($rentalAgreement->state) {
                    RentalAgreementCauseStateEnum::ACTIVE => RentalAgreementCauseStateEnum::ACTIVE,
                    default                               => RentalAgreementCauseStateEnum::DRAFT
                };
                StoreRentalAgreementClause::run($rentalAgreement, $data);
            }
        }

        $webUser  = $fulfilmentCustomer->customer->webUsers()->where('username', Arr::get($modelData, 'username'))->first();
        $password = Str::random(8);

        if(!$webUser) {
            $webUser = StoreWebUser::make()->action($fulfilmentCustomer->customer, [
                'email'    => Arr::get($modelData, 'email'),
                'username' => Arr::get($modelData, 'username'),
                'password' => $password,
                'is_root'  => true
            ]);
        }

        UpdateWebUser::make()->action($webUser, [
            'email'    => Arr::get($modelData, 'email'),
            'username' => Arr::get($modelData, 'username')
        ]);

        $webUser->notify(new SendEmailRentalAgreementCreated($password));
        StoreRentalAgreementSnapshot::run($rentalAgreement, firstSnapshot: true);


        FulfilmentCustomerHydrateStatus::run($fulfilmentCustomer);


        return $rentalAgreement;
    }

    public function rules(): array
    {
        return [
            'billing_cycle'                           => ['required', Rule::enum(RentalAgreementBillingCycleEnum::class)],
            'pallets_limit'                           => ['nullable', 'integer', 'min:1', 'max:10000'],
            'clauses'                                 => ['sometimes', 'array'],
            'clauses.rentals.*.asset_id'              => [
                'sometimes',
                Rule::exists('assets', 'id')
                    ->where('shop_id', $this->fulfilment->shop_id)
            ],
            'clauses.rentals.*.percentage_off'        => ['sometimes', 'numeric', 'gt:0'],
            'clauses.services.*.asset_id'             => [
                'sometimes',
                Rule::exists('assets', 'id')
                    ->where('shop_id', $this->fulfilment->shop_id)
            ],
            'clauses.services.*.percentage_off'       => ['sometimes', 'numeric', 'gt:0'],
            'clauses.physical_goods.*.asset_id'       => [
                'sometimes',
                Rule::exists('assets', 'id')
                    ->where('shop_id', $this->fulfilment->shop_id)
            ],
            'clauses.physical_goods.*.percentage_off' => ['sometimes', 'numeric', 'gt:0'],
            'state'                                   => ['sometimes', Rule::enum(RentalAgreementStateEnum::class)],
            'created_at'                              => ['sometimes', 'date'],
            'username'                                => [
                'required',
                'string',
                'max:255',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->shop->website->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                    ]
                ),
            ],
            'email' => [
                'email',
                'max:255',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->shop->website->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                    ]
                ),
            ]
        ];
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData): RentalAgreement
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function prepareForValidation(): void
    {
        $clauses = $this->get('clauses', []);
        foreach ($clauses as $clauseType => $clauseData) {
            foreach ($clauseData as $key => $clause) {
                if (!Arr::get($clause, 'percentage_off', 0)) {
                    unset($clauses[$clauseType][$key]);
                }
            }
        }
        $this->set('clauses', $clauses);
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): RentalAgreement
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

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
