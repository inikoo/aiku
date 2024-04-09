<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\Accounting\OrgPaymentServiceProvider\Hydrators\OrgPaymentServiceProviderHydratePaymentAccounts;
use App\Actions\Accounting\PaymentServiceProvider\Hydrators\PaymentServiceProviderHydratePaymentAccounts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydratePaymentAccounts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePaymentAccounts;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
use App\Rules\IUnique;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePaymentAccount extends OrgAction
{
    public function handle(OrgPaymentServiceProvider $orgPaymentServiceProvider, array $modelData): PaymentAccount
    {
        data_set($modelData, 'group_id', $orgPaymentServiceProvider->group_id);
        data_set($modelData, 'organisation_id', $orgPaymentServiceProvider->organisation_id);
        data_set($modelData, 'payment_service_provider_id', $orgPaymentServiceProvider->payment_service_provider_id);
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $orgPaymentServiceProvider->accounts()->create($modelData);
        $paymentAccount->stats()->create();

        if($paymentAccount->type==PaymentAccountTypeEnum::ACCOUNT) {
            $paymentAccount->serialReferences()->create(
                [
                    'model'           => SerialReferenceModelEnum::PAYMENT,
                    'organisation_id' => $paymentAccount->organisation->id,
                    'format'          => $paymentAccount->slug.'-%04d'
                ]
            );
        }

        PaymentServiceProviderHydratePaymentAccounts::dispatch($orgPaymentServiceProvider->paymentServiceProvider);
        OrganisationHydratePaymentAccounts::dispatch($orgPaymentServiceProvider->organisation);
        GroupHydratePaymentAccounts::dispatch($orgPaymentServiceProvider->group);
        OrgPaymentServiceProviderHydratePaymentAccounts::dispatch($orgPaymentServiceProvider);



        return $paymentAccount;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("accounting.edit");
    }

    public function rules(): array
    {
        return [
            'type'      => ['required', Rule::in(PaymentAccountTypeEnum::values())],
            'code'      => [
                'required',
                'between:2,16',
                'alpha_dash',
                new IUnique(
                    table: 'payment_accounts',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),
            ],
            'name'       => ['required', 'max:250', 'string'],
            'is_accounts'=> ['sometimes', 'boolean'],
            'source_id'  => ['sometimes', 'string'],
        ];
    }

    public function action(OrgPaymentServiceProvider $orgPaymentServiceProvider, array $modelData): PaymentAccount
    {
        $this->asAction = true;
        $this->initialisation($orgPaymentServiceProvider->organisation, $modelData);

        return $this->handle($orgPaymentServiceProvider, $this->validatedData);
    }

    public string $commandSignature = 'payment-account:create {provider} {type}';

    public function asCommand(Command $command): int
    {
        $provider = OrgPaymentServiceProvider::where('slug', $command->argument('provider'))->first();
        $type     = $command->argument('type');

        $publicKey = $command->ask('Your public key: ');
        $secretKey = $command->ask('Your secret key: ');
        $channelId = $command->ask('Your channel id: ');

        $modelData = [
            'code' => rand(001, 999),
            'type' => $type,
            'name' => 'Account Checkout',
            'data' => [
                'public_key' => $publicKey,
                'secret_key' => $secretKey,
                'channel_id' => $channelId
            ]
        ];

        $this->handle($provider, $modelData);

        return 0;
    }
}
