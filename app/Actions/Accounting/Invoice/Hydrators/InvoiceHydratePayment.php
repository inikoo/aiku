<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 16-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\Invoice\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Invoice\InvoicePayStatusEnum;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class InvoiceHydratePayment
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
        $newData = [];
        $paymentsCount = $invoice->payments()->count();
        $dateThreeYearsAgo = now()->subYears(3);

        if ($paymentsCount == 0 && $invoice->date <= $dateThreeYearsAgo) {
            $newData['pay_status'] = InvoicePayStatusEnum::UNKNOWN->value;
        } elseif ($paymentsCount > 0) {
            $lastPayment = $invoice->payments()->latest('created_at')->first();
            $newData['paid_at'] = $lastPayment->date;
            $newData['payment_amount'] = $invoice->payments()->sum('payments.amount');

            if ($newData['payment_amount'] < 0) {
                $newData['type'] = InvoiceTypeEnum::REFUND->value;
            }

            if ($newData['payment_amount'] >= $invoice->total_amount) {
                $newData['pay_status'] = InvoicePayStatusEnum::PAID->value;
            } else {
                $newData['pay_status'] = InvoicePayStatusEnum::UNPAID->value;
            }
        } else {
            $newData['pay_status'] = InvoicePayStatusEnum::UNPAID->value;
        }

        $invoice->update($newData);
    }

    public string $commandSignature = 'hydrate:invoice_payment';

    public function asCommand()
    {
        Invoice::chunk(1000, function ($invoices) {
            foreach ($invoices as $invoice) {
                print "Hydrating invoice {$invoice->id}\n";
                $this->handle($invoice);
            }
        });
    }

}
