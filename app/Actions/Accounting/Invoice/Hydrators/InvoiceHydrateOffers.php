<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-14h-22m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Accounting\Invoice\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Accounting\Invoice;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class InvoiceHydrateOffers
{
    use AsAction;
    use WithEnumStats;
    private Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->invoice->id))->dontRelease()];
    }
    public function handle(Invoice $invoice): void
    {

        $stats = [
            'number_offer_components' => $invoice->invoiceTransactions()->sum(function ($transaction) {
                return $transaction->countOfferComponents();
            }),
            'number_offers' => $invoice->invoiceTransactions()->sum(function ($transaction) {
                return $transaction->countOffers();
            }),
            'number_offer_campaigns' => $invoice->invoiceTransactions()->sum(function ($transaction) {
                return $transaction->countOfferCampaigns();
            }),
        ];


        $invoice->stats()->update($stats);
    }

}
