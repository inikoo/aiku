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
                return $this->countOfferComponents($transaction);
            }),
            'number_offers' => $invoice->invoiceTransactions()->sum(function ($transaction) {
                return $this->countOffers($transaction);
            }),
            'number_offer_campaigns' => $invoice->invoiceTransactions()->sum(function ($transaction) {
                return $this->countOfferCampaigns($transaction);
            }),
        ];


        $invoice->stats()->update($stats);
    }

    public function countOfferComponents($transaction): int
    {
        return $transaction->offerComponents()
            ->distinct('offer_component_id')
            ->count('offer_component_id');
    }

    public function countOffers($transaction): int
    {
        return $transaction->offerComponents()
            ->distinct('offer_id')
            ->count('offer_id');
    }

    public function countOfferCampaigns($transaction): int
    {
        return $transaction->offerComponents()
            ->distinct('offer_campaigns_id')
            ->count('offer_campaigns_id');
    }


}
