<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\Accounting\PaymentServiceProvider\Hydrators\PaymentServiceProviderHydrateAccounts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateAccounting;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePaymentAccount
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Organisation $organisation, PaymentServiceProvider $paymentServiceProvider, array $modelData): PaymentAccount
    {
        data_set($modelData, 'organisation_id', $organisation->id);
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $paymentServiceProvider->accounts()->create($modelData);
        $paymentAccount->stats()->create();
        PaymentServiceProviderHydrateAccounts::dispatch($paymentServiceProvider);
        OrganisationHydrateAccounting::dispatch($organisation);

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
            'code' => ['required', 'unique:payment_accounts', 'between:2,9', 'alpha_dash'],
            'name' => ['required', 'max:250', 'string'],
        ];
    }

    public function action(Organisation $organisation, PaymentServiceProvider $paymentServiceProvider, array $modelData): PaymentAccount
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($organisation, $paymentServiceProvider, $validatedData);
    }
}
