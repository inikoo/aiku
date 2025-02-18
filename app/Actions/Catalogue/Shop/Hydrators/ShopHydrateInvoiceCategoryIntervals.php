<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 18-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceCategory;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateInvoiceCategoryIntervals
{
    use AsAction;
    use WithIntervalsAggregators;

    public string $jobQueue = 'sales';

    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }

    public function handle(Shop $shop): void
    {

        $stats = [];

        $invoices = Invoice::where('shop_id', $shop->id)->whereNotNull('invoice_category_id')->where('type', InvoiceTypeEnum::INVOICE)->groupBy("invoice_category_id")->pluck("invoice_category_id");
        $invoiceCategories = InvoiceCategory::whereIn('id', $invoices)->get();

        // $invoiceCategories = InvoiceCategory::whereIn('invoice_id', $invoices)->get();

        // $stats     = $this->getIntervalsData(
        //     stats: $stats,
        //     queryBase: $queryBase,
        //     statField: 'invoices_'
        // );

        // $queryBase = Invoice::where('shop_id', $shop->id)->where('type', InvoiceTypeEnum::REFUND)->selectRaw(' count(*) as  sum_aggregate');
        // $stats     = $this->getIntervalsData(
        //     stats: $stats,
        //     queryBase: $queryBase,
        //     statField: 'refunds_'
        // );


        // $shop->orderingIntervals()->update($stats);
    }


    public string $commandSignature = 'shop:invoice_categories';

    public function asCommand($command)
    {
        $f = Shop::find(1);
        $this->handle($f);
    }

}
