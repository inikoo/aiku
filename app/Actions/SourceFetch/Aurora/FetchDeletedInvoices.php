<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Feb 2023 15:21:25 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Sales\Invoice\StoreInvoice;
use App\Actions\Sales\Invoice\UpdateInvoice;
use App\Models\Sales\Invoice;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchDeletedInvoices extends FetchAction
{
    public string $commandSignature = 'fetch:deleted-invoices {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Invoice
    {
        if ($deletedInvoiceData = $tenantSource->fetchDeletedInvoice($tenantSourceId)) {
            if ($deletedInvoiceData['invoice']) {
                if ($invoice = Invoice::withTrashed()->where('source_id', $deletedInvoiceData['invoice']['source_id'])
                    ->first()) {
                    $invoice = UpdateInvoice::run(
                        invoice:   $invoice,
                        modelData: $deletedInvoiceData['invoice'],
                    );
                } else {
                    $invoice = StoreInvoice::run(
                        order:              $deletedInvoiceData['order'],
                        modelData:          $deletedInvoiceData['invoice'],
                        billingAddress: $deletedInvoiceData['billing_address']
                    );
                }


                DB::connection('aurora')->table('Invoice Deleted Dimension')
                    ->where('Invoice Deleted Key', $invoice->source_id)
                    ->update(['aiku_id' => $invoice->id]);

                return $invoice;
            }
        }

        return null;
    }


    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Invoice Deleted Dimension')
            ->select('Invoice Deleted Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Invoice Deleted Dimension')->count();
    }
}
