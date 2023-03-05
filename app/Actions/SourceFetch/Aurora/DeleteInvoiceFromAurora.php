<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Feb 2023 11:14:57 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Sales\Invoice\DeleteInvoice;
use App\Models\Sales\Invoice;
use App\Services\Tenant\SourceTenantService;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteInvoiceFromAurora
{
    use AsAction;

    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Invoice
    {
        if ($invoice = Invoice::withTrashed()->where('source_id', $tenantSourceId)->first()) {
            if (!$invoice->trashed()) {
                $deletedInvoiceData = $tenantSource->fetchDeletedInvoice($tenantSourceId);

                DeleteInvoice::run($invoice, [
                    'data' => [
                        'deleted' => ['note' => $deletedInvoiceData['note']]
                    ]
                ]);
            }
        } else {
            return FetchDeletedInvoices::run($tenantSource, $tenantSourceId);
        }

        return null;
    }
}
