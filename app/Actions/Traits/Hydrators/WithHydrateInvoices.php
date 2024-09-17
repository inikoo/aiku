<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 14 Sept 2024 21:06:37 Malaysia Time, Plane KL - Taipei
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Hydrators;

use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;

trait WithHydrateInvoices
{
    public function getInvoicesStats(Group|Organisation|Shop|Customer $model): array
    {
        $numberInvoices = $model->invoices()->count();
        $stats          = [
            'number_invoices'              => $numberInvoices,
            'number_invoices_type_invoice' => $model->invoices()->where('type', InvoiceTypeEnum::INVOICE)->count(),
            'last_invoiced_at'             => $model->invoices()->max('date'),
            'invoiced_net_amount'          => $model->invoices()->sum('net_amount'),
            'invoiced_org_net_amount'      => $model->invoices()->sum('org_net_amount'),
            'invoiced_grp_net_amount'      => $model->invoices()->sum('grp_net_amount'),
        ];
        $stats['number_invoices_type_refund'] = $stats['number_invoices'] - $stats['number_invoices_type_invoice'];

        return $stats;
    }
}
