<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Accounting\PaymentAccount\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\PaymentAccount;
use Illuminate\Support\Collection;

class ReindexPaymentAccountSearch extends HydrateModel
{
    public string $commandSignature = 'payment_accounts:search {organisations?*} {--s|slugs=} ';


    public function handle(PaymentAccount $paymentAccount): void
    {
        PaymentAccountRecordSearch::run($paymentAccount);
    }

    protected function getModel(string $slug): PaymentAccount
    {
        return PaymentAccount::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return PaymentAccount::withTrashed()->get();
    }
}
