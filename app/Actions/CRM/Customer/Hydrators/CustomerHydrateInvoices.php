<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\Traits\WithElasticsearch;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Enums\CRM\Customer\CustomerTradeStateEnum;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateInvoices
{
    use AsAction;
    use WithElasticsearch;
    use WithEnumStats;
    private Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->customer->id))->dontRelease()];
    }

    public function handle(Customer $customer): void
    {

        $numberInvoices = $customer->invoices()->count();
        $stats          = [
            'number_invoices' => $numberInvoices,
        ];

        $updateData['trade_state']= match ($numberInvoices) {
            0       => CustomerTradeStateEnum::NONE,
            1       => CustomerTradeStateEnum::ONE,
            default => CustomerTradeStateEnum::MANY
        };

        $stats=array_merge($stats, $this->getEnumStats(
            model:'invoices',
            field: 'type',
            enum: InvoiceTypeEnum::class,
            models: Invoice::class,
            where: function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }
        ));

        $customer->update($updateData);
        $customer->stats()->update($stats);
    }

}
