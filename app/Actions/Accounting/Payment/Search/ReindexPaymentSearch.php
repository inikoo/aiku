<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Accounting\Payment\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\Payment;
use Illuminate\Support\Collection;

class ReindexPaymentSearch extends HydrateModel
{
    public string $commandSignature = 'payments:search {organisations?*} {--s|slugs=} ';


    public function handle(Payment $payment): void
    {
        PaymentRecordSearch::run($payment);
    }

    protected function getModel(string $slug): Payment
    {
        return Payment::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Payment::withTrashed()->get();
    }
}
