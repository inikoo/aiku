<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 19:32:35 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\Invoice\UpdateInvoice;
use App\Models\Accounting\Invoice;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraInvoices extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:invoices {organisations?*} {--s|source_id=} {--S|shop= : Shop slug}  {--N|only_new : Fetch only new} {--w|with=* : Accepted values: transactions} {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId, bool $forceWithTransactions = false): ?Invoice
    {
        $doTransactions = false;
        if (in_array('transactions', $this->with) or $forceWithTransactions) {
            $doTransactions = true;
        }

        if ($invoiceData = $organisationSource->fetchInvoice($organisationSourceId, $doTransactions)) {
            if ($invoice = Invoice::withTrashed()->where('source_id', $invoiceData['invoice']['source_id'])
                ->first()) {
                try {
                    UpdateInvoice::make()->action(
                        invoice: $invoice,
                        modelData: $invoiceData['invoice'],
                        hydratorsDelay: 60,
                        strict: false,
                        audit: false
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $invoiceData['invoice'], 'Invoice', 'update');

                    return null;
                }


                if ($doTransactions) {
                    $this->fetchInvoiceTransactions($organisationSource, $invoice);
                    $this->fetchInvoiceNoProductTransactions($organisationSource, $invoice);
                }


                return $invoice;
            } else {
                if ($invoiceData['invoice']) {
                    if ($invoiceData['invoice']['data']['foot_note'] == '') {
                        unset($invoiceData['invoice']['data']['foot_note']);
                    }
                    try {
                        $invoice = StoreInvoice::make()->action(
                            parent: $invoiceData['parent'],
                            modelData: $invoiceData['invoice'],
                            hydratorsDelay: $this->hydratorsDelay,
                            strict: false,
                            audit: false
                        );

                        Invoice::enableAuditing();
                        $this->saveMigrationHistory(
                            $invoice,
                            Arr::except($invoiceData['invoice'], ['fetched_at', 'last_fetched_at', 'source_id'])
                        );


                        $this->recordNew($organisationSource);

                        $sourceData = explode(':', $invoice->source_id);
                        DB::connection('aurora')->table('Invoice Dimension')
                            ->where('Invoice Key', $sourceData[1])
                            ->update(['aiku_id' => $invoice->id]);
                    } catch (Exception|Throwable $e) {
                        $this->recordError($organisationSource, $e, $invoiceData['invoice'], 'Invoice', 'store');

                        return null;
                    }
                    if (in_array('transactions', $this->with) or $forceWithTransactions) {
                        $this->fetchInvoiceTransactions($organisationSource, $invoice);
                        $this->fetchInvoiceNoProductTransactions($organisationSource, $invoice);
                    }



                    return $invoice;
                }
                print "Warning invoice $organisationSourceId do not have customer\n";
            }
        }

        return null;
    }


    private function fetchInvoiceTransactions($organisationSource, Invoice $invoice): void
    {
        $transactionsToDelete = $invoice->invoiceTransactions()->whereIn('model_type', ['Product', 'Service'])->pluck('source_id', 'id')->all();
        $this->allowLegacy    = true;

        $sourceData = explode(':', $invoice->source_id);

        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Invoice Key', $sourceData[1])
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$organisationSource->getOrganisation()->id.':'.$auroraData->{'Order Transaction Fact Key'}]);
            FetchInvoiceTransactions::run($organisationSource, $auroraData->{'Order Transaction Fact Key'}, $invoice);
        }
        $invoice->invoiceTransactions()->whereIn('id', array_keys($transactionsToDelete))->delete();
    }

    private function fetchInvoiceNoProductTransactions($organisationSource, Invoice $invoice): void
    {
        $transactionsToDelete = $invoice->invoiceTransactions()->whereNotIn('model_type', ['Product', 'Service'])->pluck('source_alt_id', 'id')->all();
        $this->allowLegacy    = true;

        $sourceData = explode(':', $invoice->source_id);

        foreach (
            DB::connection('aurora')
                ->table('Order No Product Transaction Fact')
                ->select('Order No Product Transaction Fact Key')
                ->where('Invoice Key', $sourceData[1])
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$organisationSource->getOrganisation()->id.':'.$auroraData->{'Order No Product Transaction Fact Key'}]);
            FetchNoProductInvoiceTransactions::run($organisationSource, $auroraData->{'Order No Product Transaction Fact Key'}, $invoice);
        }
        $invoice->invoiceTransactions()->whereIn('id', array_keys($transactionsToDelete))->delete();
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Invoice Dimension')
            ->select('Invoice Key as source_id')
            ->orderBy('Invoice Date');

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Invoice Store Key', $sourceData[1]);
        }
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        $query->orderBy('Invoice Date');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Invoice Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Invoice Store Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Invoice Dimension')->update(['aiku_id' => null]);
    }


}
