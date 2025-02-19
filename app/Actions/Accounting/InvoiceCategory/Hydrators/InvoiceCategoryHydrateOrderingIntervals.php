<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 19-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\InvoiceCategory\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceCategory;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class InvoiceCategoryHydrateOrderingIntervals
{
    use AsAction;
    use WithIntervalsAggregators;

    public string $jobQueue = 'orders';

    private InvoiceCategory $invoicecategory;

    public function __construct(InvoiceCategory $invoicecategory)
    {
        $this->invoicecategory = $invoicecategory;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->invoicecategory->id))->dontRelease()];
    }

    public function handle(InvoiceCategory $invoiceCategory): void
    {

        $stats = [];

        $queryBase = Invoice::where('invoice_category_id', $invoiceCategory->id)->where('type', InvoiceTypeEnum::INVOICE)->selectRaw('count(*) as  sum_aggregate');
        $stats     = $this->getIntervalsData(
            stats: $stats,
            queryBase: $queryBase,
            statField: 'invoices_'
        );

        $queryBase = Invoice::where('invoice_category_id', $invoiceCategory->id)->where('type', InvoiceTypeEnum::REFUND)->selectRaw(' count(*) as  sum_aggregate');
        $stats     = $this->getIntervalsData(
            stats: $stats,
            queryBase: $queryBase,
            statField: 'refunds_'
        );

        $invoiceCategory->orderingIntervals->update($stats);
    }

    public string $commandSignature = 'invoice_category:hydrate_ordering_intervals';

    public function asCommand($command)
    {
        $f = InvoiceCategory::all();
        foreach ($f as $key => $value) {
            $this->handle($value);
        }
    }

}
