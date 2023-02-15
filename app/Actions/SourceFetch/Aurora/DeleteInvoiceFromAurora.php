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
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class DeleteInvoiceFromAurora extends FetchAction
{

    public string $commandSignature = 'fetch:delete-invoice {tenants?*} {--s|source_id=} ';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Invoice
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


    function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Invoice Dimension')
            ->select('Invoice Key as source_id')
            ->orderBy('Invoice Date');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query;
    }

    function count(): ?int
    {
        $query = DB::connection('aurora')->table('Invoice Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

}
