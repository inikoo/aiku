<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\Payment\Hydrators\PaymentHydrateUniversalSearch;
use App\Actions\Accounting\PaymentAccount\Hydrators\PaymentAccountHydratePayments;
use App\Actions\Accounting\PaymentServiceProvider\Hydrators\PaymentServiceProviderHydratePayments;
use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\Market\Shop\Hydrators\ShopHydratePayments;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePayments;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\CRM\Customer;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePayment extends OrgAction
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Customer $customer, PaymentAccount $paymentAccount, array $modelData): Payment
    {
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'payment_service_provider_id', $paymentAccount->payment_service_provider_id);
        data_set($modelData, 'customer_id', $customer->id);
        data_set($modelData, 'shop_id', $customer->shop_id);
        data_fill($modelData, 'currency_id', $customer->shop->currency_id);
        data_fill($modelData, 'oc_amount', GetCurrencyExchange::run($customer->shop->currency, $paymentAccount->organisation->currency));
        data_fill($modelData, 'gc_amount', GetCurrencyExchange::run($customer->shop->currency, $paymentAccount->organisation->group->currency));
        data_fill($modelData, 'date', gmdate('Y-m-d H:i:s'));

        /** @var Payment $payment */
        $payment = $paymentAccount->payments()->create($modelData);

        OrganisationHydratePayments::dispatch($paymentAccount->organisation)->delay($this->hydratorsDelay);
        PaymentServiceProviderHydratePayments::dispatch($payment->paymentAccount->paymentServiceProvider)->delay($this->hydratorsDelay);
        PaymentAccountHydratePayments::dispatch($payment->paymentAccount)->delay($this->hydratorsDelay);
        ShopHydratePayments::dispatch($payment->shop)->delay($this->hydratorsDelay);


        PaymentHydrateUniversalSearch::dispatch($payment);

        return $payment;
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
            'reference'    => ['nullable', 'string', 'max:255'],
            'amount'       => ['required', 'decimal:0,2'],
            'oc_amount'    => ['required', 'decimal:0,2'],
            'data'         => ['sometimes', 'array'],
            'currency_id'  => ['required', 'exists:currencies,id'],
            'date'         => ['required', 'date'],
            'created_at'   => ['sometimes', 'date'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
            'cancelled_at' => ['sometimes', 'nullable', 'date'],
            'status'       => ['sometimes', 'required', Rule::enum(PaymentStatusEnum::class)],
            'state'        => ['sometimes', 'required', Rule::enum(PaymentStateEnum::class)],
            'source_id'    => ['sometimes', 'string'],
        ];
    }

    public function action(Customer $customer, PaymentAccount $paymentAccount, array $modelData, int $hydratorsDelay = 0): Payment
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $paymentAccount, $this->validatedData);
    }
}
