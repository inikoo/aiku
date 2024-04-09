<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\Accounting\PaymentServiceProvider\Hydrators\PaymentServiceProviderHydratePaymentAccounts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydratePaymentAccounts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePaymentAccounts;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Rules\IUnique;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePaymentAccount extends OrgAction
{
    public string $commandSignature = 'payment-account:create {provider} {type}';

    public function handle(PaymentServiceProvider $paymentServiceProvider, array $modelData): PaymentAccount
    {
        data_set($modelData, 'group_id', 1);
        data_set($modelData, 'organisation_id', 1);

        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $paymentServiceProvider->accounts()->create($modelData);
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



        PaymentServiceProviderHydratePaymentAccounts::dispatch($paymentServiceProvider);
        // OrganisationHydratePaymentAccounts::dispatch($paymentServiceProvider->organisation);
        // GroupHydratePaymentAccounts::dispatch($paymentServiceProvider->group);

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

    public function action(PaymentServiceProvider $paymentServiceProvider, array $modelData): PaymentAccount
    {
        $this->asAction = true;
        $this->initialisation($paymentServiceProvider->organisation, $modelData);

        return $this->handle($paymentServiceProvider, $this->validatedData);
    }

    public function asCommand(Command $command): int
    {
        $provider = PaymentServiceProvider::where('slug', $command->argument('provider'))->first();
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
