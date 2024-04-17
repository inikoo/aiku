<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount\Types;

use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\OrgAction;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;

class StoreCashPaymentAccount extends OrgAction
{
    public OrgPaymentServiceProvider|PaymentServiceProvider $parent;

    public function handle(OrgPaymentServiceProvider|PaymentServiceProvider $parent, array $modelData): PaymentAccount
    {
        return StorePaymentAccount::run($parent, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
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
            'name'       => ['required', 'max:250', 'string']
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): PaymentAccount
    {
        $this->asAction = true;

        $this->fillFromRequest($request);
        $this->initialisation($organisation, $request);

        /** @var PaymentServiceProvider $paymentServiceProvider */
        $paymentServiceProvider = $organisation
            ->paymentServiceProviders()
            ->where('code', $organisation->slug.'-'.Str::replace('-', '', $request->input('type')))
            ->first();

        return $this->handle($paymentServiceProvider, $this->validatedData);
    }

    public function asPaymentServiceProvider(Organisation $organisation, PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): PaymentAccount
    {
        $this->asAction = true;

        $this->fillFromRequest($request);
        $this->initialisation($organisation, $request);

        return $this->handle($paymentServiceProvider, $this->validatedData);
    }

    public function asOrgPaymentServiceProvider(Organisation $organisation, OrgPaymentServiceProvider $orgPaymentServiceProvider, ActionRequest $request): PaymentAccount
    {
        $this->asAction = true;

        $this->fillFromRequest($request);
        $this->initialisation($organisation, $request);

        return $this->handle($orgPaymentServiceProvider, $this->validatedData);
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
