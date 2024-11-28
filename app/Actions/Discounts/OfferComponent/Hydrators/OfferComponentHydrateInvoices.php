<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-11h-59m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Discounts\OfferComponent\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Discounts\OfferComponent;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OfferComponentHydrateInvoices
{
    use AsAction;
    use WithEnumStats;

    private OfferComponent $offerComponent;

    public function __construct(OfferComponent $offerComponent)
    {
        $this->offerComponent = $offerComponent;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->offerComponent->id))->dontRelease()];
    }

    public function handle(OfferComponent $offerComponent): void
    {
        $stats = [
            'number_invoices' => $offerComponent->invoiceTransactions()->distinct()->count('invoice_transaction_has_offer_components.invoice_id')
        ];

        $offerComponent->stats()->update($stats);
    }


}
