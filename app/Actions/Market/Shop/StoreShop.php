<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:35:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Market\Shop;

use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Mail\Outbox\StoreOutbox;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Enums\Market\Shop\ShopSubtypeEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\Mail\Mailroom;
use App\Models\Market\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShop
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(array $modelData): Shop
    {
        $tenant = app('currentTenant');
        /** @var Shop $shop */
        $shop = Shop::create($modelData);
        $shop->stats()->create();
        $shop->accountingStats()->create();
        $shop->mailStats()->create();
        $shop->crmStats()->create();
        $shop->apiTenantUser()->create();

        /** @noinspection DuplicatedCode */
        $shop->serialReferences()->create(
            [
                'model'     => SerialReferenceModelEnum::CUSTOMER,
                'tenant_id' => $tenant->id,
            ]
        );
        $shop->serialReferences()->create(
            [
                'model'     => SerialReferenceModelEnum::ORDER,
                'tenant_id' => $tenant->id,
            ]
        );


        SetCurrencyHistoricFields::run($shop->currency, $shop->created_at);

        $paymentAccount       = StorePaymentAccount::run($tenant->accountsServiceProvider(), [
            'code' => 'accounts-'.$shop->slug,
            'name' => 'Accounts '.$shop->code,
            'data' => [
                'service-code' => 'accounts'
            ]
        ]);
        $paymentAccount->slug = 'accounts-'.$shop->slug;
        $paymentAccount->save();
        $shop = AttachPaymentAccountToShop::run($shop, $paymentAccount);

        foreach (OutboxTypeEnum::cases() as $case) {
            if ($case->scope() == 'shop') {
                $mailroom = Mailroom::where('code', $case->mailroomCode()->value)->first();

                StoreOutbox::run(
                    $mailroom,
                    [
                        'shop_id' => $shop->id,
                        'name'    => $case->label(),
                        'type'    => str($case->value)->camel()->kebab()->value(),

                    ]
                );
            }
        }

        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $request->merge(
            [
                'type' => match ($this->get('subtype')) {
                    'fulfilment'=> 'fulfilment-house',
                    'b2b','b2c','dropshipping'=>'shop',

                }
            ]
        );
    }

    public function rules(): array
    {
        return [
            'name'                     => ['required', 'string', 'max:255'],
            'code'                     => ['required', 'unique:tenant.shops', 'between:2,4', 'alpha_dash'],
            'contact_name'             => ['nullable', 'string', 'max:255'],
            'company_name'             => ['nullable', 'string', 'max:255'],
            'email'                    => ['nullable', 'email'],
            'phone'                    => 'nullable',
            'identity_document_number' => ['nullable', 'string'],
            'identity_document_type'   => ['nullable', 'string'],
            'type'                     => ['required', Rule::in(ShopTypeEnum::values())],
            'subtype'                  => ['required', Rule::in(ShopSubtypeEnum::values())],
            'country_id'               => ['required', 'exists:central.countries,id'],
            'currency_id'              => ['required', 'exists:central.currencies,id'],
            'language_id'              => ['required', 'exists:central.languages,id'],
            'timezone_id'              => ['required', 'exists:central.timezones,id'],
        ];
    }


    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($request->get('identity_document_number') and !$request->get('identity_document_type')) {
            $validator->errors()->add('contact_name', 'document type required');
        }
        if ($request->get('identity_document_type') and !$request->get('identity_document_number')) {
            $validator->errors()->add('contact_name', 'document number required');
        }
    }

    public function action(array $objectData): Shop
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }

    public function asController(ActionRequest $request): Shop
    {
        $this->fillFromRequest($request);
        $request->validate();

        return $this->handle($request->validated());
    }

    public function htmlResponse(Shop $shop): RedirectResponse
    {
        return Redirect::route('shops.show', $shop->slug);
    }
}
