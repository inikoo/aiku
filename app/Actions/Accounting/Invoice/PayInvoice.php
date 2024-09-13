<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\CreditTransaction\StoreCreditTransaction;
use App\Actions\Accounting\Payment\StorePayment;
use App\Actions\OrgAction;
use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\CRM\Customer;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class PayInvoice extends OrgAction
{
    public function handle(Invoice $invoice, Customer $customer, PaymentAccount $paymentAccount, array $modelData): Payment
    {
        $payment = StorePayment::make()->action($customer, $paymentAccount, $modelData);

        AttachPaymentToInvoice::make()->action($invoice, $payment, [
            'amount'    => Arr::get($modelData, 'amount'),
            'reference' => Arr::get($modelData, 'reference')
        ]);

        return $payment;
    }

    public function rules()
    {
        return [
            'amount'       => ['required', 'decimal:0,2'],
            'reference'    => ['nullable', 'string', 'max:255'],
            'status'       => ['sometimes', 'required', Rule::enum(PaymentStatusEnum::class)],
            'state'        => ['sometimes', 'required', Rule::enum(PaymentStateEnum::class)],
        ];
    }

    public function asController(Invoice $invoice, Customer $customer, PaymentAccount $paymentAccount, ActionRequest $request): void
    {
        $this->initialisationFromShop($customer->shop, $request);

        $this->handle($invoice, $customer, $paymentAccount,  $this->validatedData);
    }
}
