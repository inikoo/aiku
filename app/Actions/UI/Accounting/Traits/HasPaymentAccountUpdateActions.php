<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 16:36:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Accounting\Traits;

use App\Actions\Accounting\PaymentAccount\Types\UpdateBankPaymentAccount;
use App\Actions\Accounting\PaymentAccount\Types\UpdateCashPaymentAccount;
use App\Actions\Accounting\PaymentAccount\Types\UpdateCheckoutPaymentAccount;
use App\Actions\Accounting\PaymentAccount\Types\UpdatePaypalPaymentAccount;
use App\Models\Accounting\PaymentAccount;

trait HasPaymentAccountUpdateActions
{
    public function paymentAccountUpdateActions($provider, PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        return match ($provider) {
            'cash'      => UpdateCashPaymentAccount::make()->action($paymentAccount, $modelData),
            'checkout'  => UpdateCheckoutPaymentAccount::make()->action($paymentAccount, $modelData),
            'paypal'    => UpdatePaypalPaymentAccount::make()->action($paymentAccount, $modelData),
            'bank'      => UpdateBankPaymentAccount::make()->action($paymentAccount, $modelData),
            default     => $paymentAccount
        };
    }
}
