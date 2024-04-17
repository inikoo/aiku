<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 20:10:35 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\OrgPaymentServiceProvider;

use App\Actions\Accounting\PaymentAccount\Types\StoreCashPaymentAccount;
use App\Actions\OrgAction;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgPaymentServiceProviderAccount extends OrgAction
{
    public function handle(PaymentServiceProvider $paymentServiceProvider, Organisation $organisation, array $modelData): PaymentAccount
    {
        $provider                  = Arr::get(explode('-', $paymentServiceProvider->code), 1);
        $orgPaymentServiceProvider = StoreOrgPaymentServiceProvider::run($paymentServiceProvider, $organisation, $modelData);

        $paymentAccount = match ($provider) {
            'cash'  => StoreCashPaymentAccount::run($orgPaymentServiceProvider, $modelData),
            default => null
        };

        return $paymentAccount;
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
            'code' => [
                'required',
                'max:16',
                'alpha_dash',
                new IUnique(
                    table: 'payment_service_providers',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),
            ],
            'source_id'    => ['sometimes', 'string'],
        ];
    }

    public function action(PaymentServiceProvider $paymentServiceProvider, Organisation $organisation, array $modelData): PaymentAccount
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);
        return $this->handle($paymentServiceProvider, $organisation, $this->validatedData);
    }

}
