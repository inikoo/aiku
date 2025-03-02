<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 19-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\InvoiceCategory\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateInvoices;
use App\Actions\Traits\WithEnumStats;
use App\Models\Accounting\InvoiceCategory;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class InvoiceCategoryHydrateInvoices
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateInvoices;


    private InvoiceCategory $invoiceCategory;

    public function __construct(InvoiceCategory $invoiceCategory)
    {
        $this->invoiceCategory = $invoiceCategory;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->invoiceCategory->id))->dontRelease()];
    }

    public function handle(InvoiceCategory $invoiceCategory): void
    {

        $stats = $this->getInvoicesStats($invoiceCategory);
        $invoiceCategory->stats()->update($stats);
    }



}
