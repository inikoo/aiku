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
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateAccounting;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\CRM\Customer;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePayment
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Customer $customer, PaymentAccount $paymentAccount, array $modelData): Payment
    {
        $modelData['customer_id'] = $customer->id;
        $modelData['shop_id']     = $customer->shop_id;

        data_fill($modelData, 'currency_id', $customer->shop->currency_id);
        data_fill($modelData, 'tc_amount', GetCurrencyExchange::run($customer->shop->currency, $paymentAccount->organisation->currency));
        data_fill($modelData, 'gc_amount', GetCurrencyExchange::run($customer->shop->currency, $paymentAccount->organisation->group->currency));
        data_fill($modelData, 'date', gmdate('Y-m-d H:i:s'));

        /** @var Payment $payment */
        $payment = $paymentAccount->payments()->create($modelData);

        OrganisationHydrateAccounting::dispatch($paymentAccount->organisation);
        PaymentServiceProviderHydratePayments::dispatch($payment->paymentAccount->paymentServiceProvider);
        PaymentAccountHydratePayments::dispatch($payment->paymentAccount);
        ShopHydratePayments::dispatch($payment->shop);


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
            'reference' => ['required', 'string'],
            'status'    => ['sometimes','required', Rule::in(PaymentStatusEnum::values())],
            'state'     => ['sometimes','required', Rule::in(PaymentStateEnum::values())],
            'amount'    => ['required','decimal:0,2']

        ];
    }

    public function action(Customer $customer, PaymentAccount $paymentAccount, array $objectData): Payment
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($customer, $paymentAccount, $validatedData);
    }
}
