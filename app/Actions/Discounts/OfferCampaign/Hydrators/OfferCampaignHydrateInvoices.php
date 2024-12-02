<?php

/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-13h-20m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Discounts\OfferCampaign\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Discounts\OfferCampaign;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OfferCampaignHydrateInvoices
{
    use AsAction;
    use WithEnumStats;

    private OfferCampaign $offerCampaign;

    public function __construct(OfferCampaign $offerCampaign)
    {
        $this->offerCampaign = $offerCampaign;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->offerCampaign->id))->dontRelease()];
    }

    public function handle(OfferCampaign $offerCampaign): void
    {
        $stats = [
            'number_invoices' => $offerCampaign->invoiceTransactions()->distinct()->count('invoice_transaction_has_offer_components.invoice_id')
        ];

        $offerCampaign->stats()->update($stats);
    }


}
