<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 22:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithInvoiceCategoryListAuthorisation;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\InvoiceCategory;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportInvoicesInInvoiceCategory extends OrgAction
{
    use WithInvoiceCategoryListAuthorisation;
    use WithInvoiceDeliveryCountry;

    public function handle(InvoiceCategory $invoiceCategory, ?string $dateFilter = null, ?Country $country = null): StreamedResponse
    {
        $query = DB::table('invoices')
            ->where('invoices.invoice_category_id', $invoiceCategory->id)
            ->where('invoices.type', InvoiceTypeEnum::INVOICE)
            ->where('invoices.in_process', false)
            ->whereNull('invoices.deleted_at')
            ->leftJoin('shops', 'invoices.shop_id', 'shops.id')
            ->leftJoin('customers', 'invoices.customer_id', 'customers.id')
            ->leftJoin('currencies', 'invoices.currency_id', 'currencies.id')
            ->leftJoin('countries as billing_countries', 'invoices.billing_country_id', 'billing_countries.id')
            ->select([
                'invoices.id',
                'invoices.reference',
                'invoices.date',
                'shops.code as shop_code',
                'customers.name as customer_name',
                'customers.company_name as customer_company',
                'delivery_countries.name as delivery_country',
                'delivery_countries.code as delivery_country_code',
                'billing_countries.name as billing_country',
                'billing_countries.code as billing_country_code',
                'currencies.code as currency_code',
                'invoices.net_amount',
                'invoices.total_amount',
                'invoices.org_net_amount',
                'invoices.pay_status',
            ]);

        $this->joinInvoiceDeliveryCountry($query);

        if ($country) {
            $query->where('delivery_countries.id', $country->id);
        }

        if ($this->invoiceCategoryViewableShopIds !== null) {
            $query->whereIn('invoices.shop_id', $this->invoiceCategoryViewableShopIds);
        }

        if ($dateFilter && str_contains($dateFilter, '-')) {
            [$start, $end] = explode('-', $dateFilter);
            $query->whereBetween('invoices.date', [
                Carbon::createFromFormat('Ymd', trim($start))->startOfDay(),
                Carbon::createFromFormat('Ymd', trim($end))->endOfDay(),
            ]);
        }

        $filename = $invoiceCategory->slug.'-invoices'.($country ? '-'.$country->code : '').($dateFilter ? '-'.$dateFilter : '').'.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Reference',
                'Date',
                'Shop',
                'Customer',
                'Company',
                'Delivery country',
                'Delivery country code',
                'Billing country',
                'Billing country code',
                'Currency',
                'Net',
                'Total',
                'Net ('.$this->organisation->currency->code.')',
                'Payment',
            ]);

            $query->orderBy('invoices.id')->chunkById(1000, function ($invoices) use ($output) {
                foreach ($invoices as $invoice) {
                    fputcsv($output, [
                        $invoice->reference,
                        Carbon::parse($invoice->date)->format('Y-m-d'),
                        $invoice->shop_code,
                        $invoice->customer_name,
                        $invoice->customer_company,
                        $invoice->delivery_country,
                        $invoice->delivery_country_code,
                        $invoice->billing_country,
                        $invoice->billing_country_code,
                        $invoice->currency_code,
                        $invoice->net_amount,
                        $invoice->total_amount,
                        $invoice->org_net_amount,
                        $invoice->pay_status,
                    ]);
                }
            }, 'invoices.id', 'id');

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->authorizeInvoiceCategoryList($request->user(), $this->organisation);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function asController(Organisation $organisation, InvoiceCategory $invoiceCategory, ActionRequest $request): StreamedResponse
    {
        $this->initialisation($invoiceCategory->organisation, $request);

        $countryCode = $request->input('country');

        return $this->handle(
            $invoiceCategory,
            Arr::get($request->input('between'), 'date'),
            $countryCode ? Country::where('code', $countryCode)->firstOrFail() : null
        );
    }
}
