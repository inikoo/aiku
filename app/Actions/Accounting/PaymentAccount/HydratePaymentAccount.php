<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:34:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\HydrateModel;
use App\Actions\Accounting\PaymentAccount\Hydrators\PaymentAccountHydratePayments;
use App\Models\Accounting\PaymentAccount;
use Illuminate\Support\Collection;

class HydratePaymentAccount extends HydrateModel
{
    public string $commandSignature = 'hydrate:payment_account {slugs?*} {--o|org=*}  {--g|group=*}   ';

    public function handle(PaymentAccount $paymentAccount): void
    {
        PaymentAccountHydratePayments::run($paymentAccount);
    }


    protected function getModel(string $slug): PaymentAccount
    {
        return PaymentAccount::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return PaymentAccount::withTrashed()->get();
    }
}
