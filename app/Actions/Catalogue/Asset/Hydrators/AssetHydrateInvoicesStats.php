<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 06-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Catalogue\Asset\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateDeliveryNotes;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Catalogue\Asset;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class AssetHydrateInvoicesStats
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateDeliveryNotes;

    private Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->asset->id))->dontRelease()];
    }

    public function handle(Asset $asset): void
    {
        $invoices = $asset->invoiceTransactions()
                ->with('invoice')
                ->get()
                ->pluck('invoice')
                ->filter()
                ->unique('id');

        $stats          = [
            'number_invoices'              => $invoices->count(),
            'number_invoices_type_invoice' => $invoices->where('type', InvoiceTypeEnum::INVOICE)->count(),
            'last_invoiced_at'             => $invoices->max('date'),
        ];

        $stats['number_invoices_type_refund'] = $stats['number_invoices'] - $stats['number_invoices_type_invoice'];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'invoices',
                field: 'type',
                enum: InvoiceTypeEnum::class,
                models: Invoice::class,
                where: function ($q) use ($invoices) {
                    $q->whereIn('id', $invoices->pluck('id'));
                }
            )
        );


        $asset->orderingStats()->update($stats);
    }
}
