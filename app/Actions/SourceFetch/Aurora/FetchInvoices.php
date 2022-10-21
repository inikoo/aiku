<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 19:32:35 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Sales\Invoice\StoreInvoice;
use App\Models\Sales\Invoice;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchInvoices extends FetchAction
{

    public string $commandSignature = 'fetch:invoices {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Invoice
    {
        if ($invoiceData = $tenantSource->fetchInvoice($tenantSourceId)) {
            if ($invoice = Invoice::where('source_id', $invoiceData['invoice']['source_id'])
                ->first()) {
                $this->fetchInvoiceTransactions($tenantSource, $invoice);
            } else {
                if ($invoiceData['invoice']) {
                    $invoice = StoreInvoice::run(
                        order: $invoiceData['order'],
                        modelData: $invoiceData['invoice'],
                        billingAddress: $invoiceData['billing_address']);
                    $this->fetchInvoiceTransactions($tenantSource, $invoice);

                    return $invoice;
                }
                print "Warning order $tenantSourceId do not have customer\n";
            }
        }

        return null;
    }

    private function fetchInvoiceTransactions($tenantSource, Invoice $invoice): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Invoice Key', $invoice->source_id)
                ->get() as $auroraData
        ) {
            fetchInvoiceTransactions::run($tenantSource, $auroraData->{'Order Transaction Fact Key'}, $invoice);
        }
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Invoice Dimension')
            ->select('Invoice Key as source_id')
            ->orderByDesc('Invoice Date');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Invoice Dimension')->count();
    }

}
