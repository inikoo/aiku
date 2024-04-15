<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Feb 2023 15:21:25 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\Invoice\UpdateInvoice;
use App\Models\Accounting\Invoice;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedInvoices extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:deleted-invoices {organisations?*} {--s|source_id=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Invoice
    {
        if ($deletedInvoiceData = $organisationSource->fetchDeletedInvoice($organisationSourceId)) {
            if ($deletedInvoiceData['invoice']) {
                if ($invoice = Invoice::withTrashed()->where('source_id', $deletedInvoiceData['invoice']['source_id'])
                    ->first()) {
                    $invoice = UpdateInvoice::make()->action(
                        invoice:   $invoice,
                        modelData: $deletedInvoiceData['invoice'],
                    );
                } else {
                    $invoice = StoreInvoice::make()->action(
                        parent:              $deletedInvoiceData['parent'],
                        modelData:          $deletedInvoiceData['invoice'],
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
