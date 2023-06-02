<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 19:32:35 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\Invoice\UpdateInvoice;
use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Helpers\Address\UpdateHistoricAddressToModel;
use App\Models\Accounting\Invoice;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchInvoices extends FetchAction
{
    public string $commandSignature = 'fetch:invoices {tenants?*} {--s|source_id=} {--N|only_new : Fetch only new} {--w|with=* : Accepted values: transactions} {--d|db_suffix=} {--r|reset}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Invoice
    {
        if ($invoiceData = $tenantSource->fetchInvoice($tenantSourceId)) {
            if ($invoice = Invoice::withTrashed()->where('source_id', $invoiceData['invoice']['source_id'])
                ->first()) {
                UpdateInvoice::run($invoice, $invoiceData['invoice']);

                $currentBillingAddress = $invoice->getAddress('billing');

                if ($currentBillingAddress->checksum != $invoiceData['billing_address']->getChecksum()) {
                    $billingAddress = StoreHistoricAddress::run($invoiceData['billing_address']);
                    UpdateHistoricAddressToModel::run($invoice, $currentBillingAddress, $billingAddress, ['scope' => 'billing']);
                }

                if (in_array('transactions', $this->with)) {
                    $this->fetchInvoiceTransactions($tenantSource, $invoice);
                }

                $this->updateAurora($invoice);

                return $invoice;
            } else {
                if ($invoiceData['invoice']) {
                    if ($invoiceData['invoice']['data']['foot_note'] == '') {
                        unset($invoiceData['invoice']['data']['foot_note']);
                    }

                    $invoice = StoreInvoice::make()->asFetch(
                        parent:          $invoiceData['parent'],
                        modelData:      $invoiceData['invoice'],
                        billingAddress: $invoiceData['billing_address'],
                        hydratorsDelay: $this->hydrateDelay
                    );
                    if (in_array('transactions', $this->with)) {
                        $this->fetchInvoiceTransactions($tenantSource, $invoice);
                    }


                    $this->updateAurora($invoice);

                    return $invoice;
                }
                print "Warning order $tenantSourceId do not have customer\n";
            }
        }

        return null;
    }

    public function updateAurora(Invoice $invoice): void
    {
        DB::connection('aurora')->table('Invoice Dimension')
            ->where('Invoice Key', $invoice->source_id)
            ->update(['aiku_id' => $invoice->id]);
    }

    private function fetchInvoiceTransactions($tenantSource, Invoice $invoice): void
    {
        $transactionsToDelete = $invoice->invoiceTransactions()->pluck('source_id', 'id')->all();

        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Invoice Key', $invoice->source_id)
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$auroraData->{'Order Transaction Fact Key'}]);
            fetchInvoiceTransactions::run($tenantSource, $auroraData->{'Order Transaction Fact Key'}, $invoice);
        }
        $invoice->invoiceTransactions()->whereIn('id', array_keys($transactionsToDelete))->delete();
    }

    public function getModelsQuery(): Builder
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

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Invoice Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Invoices Dimension')->update(['aiku_id' => null]);
    }
}
