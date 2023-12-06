<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 15:57:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceFetch\Aurora\FetchCustomers;
use App\Actions\SourceFetch\Aurora\FetchDeletedCustomers;
use App\Actions\SourceFetch\Aurora\FetchDeletedEmployees;
use App\Actions\SourceFetch\Aurora\FetchDeletedGuests;
use App\Actions\SourceFetch\Aurora\FetchDeletedStocks;
use App\Actions\SourceFetch\Aurora\FetchDepartments;
use App\Actions\SourceFetch\Aurora\FetchDispatchedEmails;
use App\Actions\SourceFetch\Aurora\FetchEmployees;
use App\Actions\SourceFetch\Aurora\FetchFamilies;
use App\Actions\SourceFetch\Aurora\FetchGuests;
use App\Actions\SourceFetch\Aurora\FetchHistoricProducts;
use App\Actions\SourceFetch\Aurora\FetchHistoricServices;
use App\Actions\SourceFetch\Aurora\FetchLocations;
use App\Actions\SourceFetch\Aurora\FetchMailshots;
use App\Actions\SourceFetch\Aurora\FetchOrders;
use App\Actions\SourceFetch\Aurora\FetchOutboxes;
use App\Actions\SourceFetch\Aurora\FetchPaymentAccounts;
use App\Actions\SourceFetch\Aurora\FetchPayments;
use App\Actions\SourceFetch\Aurora\FetchPaymentServiceProviders;
use App\Actions\SourceFetch\Aurora\FetchProducts;
use App\Actions\SourceFetch\Aurora\FetchProspects;
use App\Actions\SourceFetch\Aurora\FetchServices;
use App\Actions\SourceFetch\Aurora\FetchShippers;
use App\Actions\SourceFetch\Aurora\FetchShops;
use App\Actions\SourceFetch\Aurora\FetchStocks;
use App\Actions\SourceFetch\Aurora\FetchTradeUnits;
use App\Actions\SourceFetch\Aurora\FetchWarehouses;
use App\Enums\Helpers\TaxNumber\TaxNumberStatusEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Dispatch\Shipper;
use App\Models\Goods\TradeUnit;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Location;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\Market\HistoricProduct;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Models\OMS\Order;
use App\Models\OMS\Transaction;
use App\Models\Procurement\Agent;
use App\Models\Procurement\AgentOrganisation;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierOrganisation;
use App\Models\SysAdmin\Guest;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait WithAuroraParsers
{
    protected function parseDate($value): ?string
    {
        return ($value                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 != '' && $value != '0000-00-00 00:00:00'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             && $value  != '2018-00-00 00:00:00') ? Carbon::parse($value)->format('Y-m-d') : null;
    }


    protected function parseTaxNumber(?string $number, ?int $countryID, array $rawData = []): ?array
    {
        if (!$number) {
            return null;
        }

        $country = Country::find($countryID);

        if ($country and (Country::isInEU($country->code) or $country->code == 'GB')) {
            if (Str::startsWith(strtolower($number), strtolower($country->code))) {
                $number = substr($number, 2);
            }
        }

        $data = [];

        if (Arr::get($rawData, 'Customer Tax Number Validation Source') == 'Online') {
            $data = [
                'data'   => [],
                'valid'  => Arr::get($rawData, 'Customer Tax Number Valid') == 'Yes',
                'status' => match (Arr::get($rawData, 'Customer Tax Number Valid')) {
                    'Yes'   => TaxNumberStatusEnum::VALID,
                    'No'    => TaxNumberStatusEnum::INVALID,
                    default => TaxNumberStatusEnum::UNKNOWN
                }

            ];

            $message = Arr::get($rawData, 'Customer Tax Number Validation Message');
            $message = preg_replace('/VIES$/', '', $message);

            if ($data['status'] != TaxNumberStatusEnum::VALID and $message != '') {
                $data['data']['exception']['message'] = $message;
            }

            $data['checked_at'] = Arr::get($rawData, 'Customer Tax Number Validation Date');
            if ($data['status'] == TaxNumberStatusEnum::INVALID) {
                $data['invalid_checked_at'] = Arr::get($rawData, 'Customer Tax Number Validation Date');
            }


            if (Arr::get($rawData, 'Customer Tax Number Valid') == 'API_Down'
                or preg_match('/failed to load|server is busy/i', $message)
            ) {
                $data['external_service_failed_at'] = Arr::get($rawData, 'Customer Tax Number Validation Date');
            }

            $data['data']['name']    = Arr::get($rawData, 'Customer Tax Number Registered Name');
            $data['data']['address'] = Arr::get($rawData, 'Customer Tax Number Registered Address');
        }


        return array_merge(
            $data,
            [
                'number'     => $number,
                'country_id' => $country?->id,
            ]
        );
    }

    protected function parseLanguageID($locale): int|null
    {
        if ($locale != '') {
            try {
                return Language::where(
                    'code',
                    match ($locale) {
                        'zh_CN.UTF-8' => 'zh-CN',
                        default       => substr($locale, 0, 2)
                    }
                )->first()->id;
            } catch (Exception) {
                //print "Locale $locale not found\n";

                return null;
            }
        }

        return null;
    }


    protected function parseCurrencyID($currencyCode): int|null
    {
        if ($currencyCode != '') {
            if ($currencyCode == 'LEU') {
                $currencyCode = 'RON';
            }

            return Currency::where('code', $currencyCode)->firstOrFail()->id;
        }

        return null;
    }

    protected function parseTimezoneID($timezone): int|null
    {
        if ($timezone != '') {
            return Timezone::where('name', $timezone)->first()->id;
        }

        return null;
    }

    protected function parseCountryID($country, $source = ''): int|null
    {
        if ($country != '') {
            try {
                if (strlen($country) == 2) {
                    return Country::withTrashed()->where('code', $country)->firstOrFail()->id;
                } elseif (strlen($country) == 3) {
                    return Country::withTrashed()->where('iso3', $country)->firstOrFail()->id;
                } else {
                    return Country::withTrashed()->where('name', $country)->firstOrFail()->id;
                }
            } catch (Exception) {
                abort(404, "Country not found: $country ($source)\n");
            }
        }

        return null;
    }

    protected function parseAddress($prefix, $auAddressData): array
    {
        $country = $auAddressData->{$prefix.' Address Country 2 Alpha Code'} ?? null;
        if ($country == 'XX') {
            $country = null;
        }

        $addressData                        = [];
        $addressData['address_line_1']      = (string)Str::of($auAddressData->{$prefix.' Address Line 1'} ?? null)->limit(251);
        $addressData['address_line_2']      = (string)Str::of($auAddressData->{$prefix.' Address Line 2'} ?? null)->limit(251);
        $addressData['sorting_code']        = (string)Str::of($auAddressData->{$prefix.' Address Sorting Code'} ?? null)->limit(187);
        $addressData['postal_code']         = (string)Str::of($auAddressData->{$prefix.' Address Postal Code'} ?? null)->limit(187);
        $addressData['locality']            = (string)Str::of($auAddressData->{$prefix.' Address Locality'} ?? null)->limit(187);
        $addressData['dependant_locality']  = (string)Str::of($auAddressData->{$prefix.' Address Dependent Locality'} ?? null)->limit(187);
        $addressData['administrative_area'] = (string)Str::of($auAddressData->{$prefix.' Address Administrative Area'} ?? null)->limit(187);
        $addressData['country_id']          = $this->parseCountryID($country, $prefix);

        return $addressData;
    }


    public function parseShop($sourceId): Shop
    {
        $shop = Shop::where('source_id', $sourceId)->first();
        if (!$shop) {
            $shop = FetchShops::run($this->tenantSource, $sourceId);
        }

        return $shop;
    }


    public function parseHistoricProduct($sourceId): HistoricProduct
    {
        $historicProduct = HistoricProduct::where('source_id', $sourceId)->first();
        if (!$historicProduct) {
            $historicProduct = FetchHistoricProducts::run($this->tenantSource, $sourceId);
        }

        return $historicProduct;
    }




    public function parseHistoricItem($sourceId): HistoricProduct
    {
        $auroraData = DB::connection('aurora')
            ->table('Product History Dimension as PH')
            ->leftJoin('Product Dimension as P', 'P.Product ID', 'PH.Product ID')
            ->select('Product Type')
            ->where('PH.Product Key', $sourceId)->first();

        $historicItem = HistoricProduct::where('source_id', $sourceId)->first();

        if ($auroraData->{'Product Type'} == 'Product') {
            if (!$historicItem) {
                $historicItem = FetchHistoricProducts::run($this->tenantSource, $sourceId);
            }
        } else {
            if (!$historicItem) {
                $historicItem = FetchHistoricServices::run($this->tenantSource, $sourceId);
            }
        }

        return $historicItem;
    }

    public function parseProduct($sourceId): Product
    {
        $product = Product::where('source_id', $sourceId)->first();
        if (!$product) {
            $product = FetchProducts::run($this->tenantSource, $sourceId);
        }

        return $product;
    }

    public function parseDepartment($sourceId): ?ProductCategory
    {
        $department = ProductCategory::where('source_department_id', $sourceId)->first();
        if (!$department) {
            $department = FetchDepartments::run($this->tenantSource, $sourceId);
        }

        return $department;
    }

    public function parseFamily($sourceId): ?ProductCategory
    {
        $family = ProductCategory::where('source_family_id', $sourceId)->first();
        if (!$family) {
            $family = FetchFamilies::run($this->tenantSource, $sourceId);
        }

        return $family;
    }

    public function parseService($sourceId): Product
    {
        $service = Product::withTrashed()->where('source_id', $sourceId)->first();
        if (!$service) {
            $service = FetchServices::run($this->tenantSource, $sourceId);
        }

        return $service;
    }

    public function parseCustomer($sourceId): ?Customer
    {
        if (!$sourceId) {
            return null;
        }
        $customer = Customer::withTrashed()->where('source_id', $sourceId)->first();
        if (!$customer) {
            $customer = FetchCustomers::run($this->tenantSource, $sourceId);
            if (!$customer) {
                $customer = FetchDeletedCustomers::run($this->tenantSource, $sourceId);
            }
        }

        return $customer;
    }

    public function parseSupplier($sourceId): ?Supplier
    {
        $supplierTenant= SupplierOrganisation::where('source_id', $sourceId)->where('tenant_id', app('currentTenant')->id)->first();
        return Supplier::withTrashed()->find($supplierTenant?->supplier_id);

    }

    public function parseAgent($sourceId): ?Agent
    {
        $agentTenant= AgentOrganisation::where('source_id', $sourceId)->where('tenant_id', app('currentTenant')->id)->first();
        return Agent::withTrashed()->find($agentTenant?->agent_id);
    }

    public function parseStock($sourceId): ?Stock
    {
        $stock = Stock::withTrashed()->where('source_id', $sourceId)->first();
        if (!$stock) {
            $stock = FetchStocks::run($this->tenantSource, $sourceId);
        }
        if (!$stock) {
            $stock = FetchDeletedStocks::run($this->tenantSource, $sourceId);
        }

        return $stock;
    }

    public function parseLocation($sourceId): Location
    {
        $location = Location::where('source_id', $sourceId)->first();
        if (!$location) {
            $location = FetchLocations::run($this->tenantSource, $sourceId);
        }

        return $location;
    }

    public function parseOrder(?int $sourceId): ?Order
    {

        if(!$sourceId) {
            return null;
        }

        $order = Order::where('source_id', $sourceId)->first();
        if (!$order) {
            $order = FetchOrders::run($this->tenantSource, $sourceId);
        }

        return $order;
    }

    public function parseTransaction($sourceId): ?Transaction
    {
        return Transaction::where('source_id', $sourceId)->first();
    }

    public function parseShipper($sourceId): Shipper
    {
        $shipper = Shipper::where('source_id', $sourceId)->first();
        if (!$shipper) {
            $shipper = FetchShippers::run($this->tenantSource, $sourceId);
        }

        return $shipper;
    }

    public function parsePaymentServiceProvider($sourceId): PaymentServiceProvider
    {
        $paymentServiceProvider = PaymentServiceProvider::where('source_id', $sourceId)->first();
        if (!$paymentServiceProvider) {
            $paymentServiceProvider = FetchPaymentServiceProviders::run($this->tenantSource, $sourceId);
        }

        return $paymentServiceProvider;
    }

    public function parsePaymentAccount($sourceId): PaymentAccount
    {
        $paymentAccount = PaymentAccount::where('source_id', $sourceId)->first();
        if (!$paymentAccount) {
            $paymentAccount = FetchPaymentAccounts::run($this->tenantSource, $sourceId);
        }

        return $paymentAccount;
    }

    public function parseEmployee($sourceId): ?Employee
    {
        $employee = Employee::withTrashed()->where('source_id', $sourceId)->first();
        if (!$employee) {
            $employee = FetchEmployees::run($this->tenantSource, $sourceId);
        }
        if (!$employee) {
            $employee = FetchDeletedEmployees::run($this->tenantSource, $sourceId);
        }

        return $employee;
    }

    public function parseGuest($sourceId): ?Guest
    {
        $guest = Guest::withTrashed()->where('source_id', $sourceId)->first();
        if (!$guest) {
            $guest = FetchGuests::run($this->tenantSource, $sourceId);
        }
        if (!$guest) {
            $guest = FetchDeletedGuests::run($this->tenantSource, $sourceId);
        }

        return $guest;
    }

    public function parseOutbox($sourceId): ?Outbox
    {
        $outbox = Outbox::where('source_id', $sourceId)->first();
        if (!$outbox) {
            $outbox = FetchOutboxes::run($this->tenantSource, $sourceId);
        }

        return $outbox;
    }

    public function parseMailshot($sourceId): ?Mailshot
    {
        if (!$sourceId) {
            return null;
        }

        $mailshot = Mailshot::where('source_id', $sourceId)->first();
        if (!$mailshot) {
            $mailshot = FetchMailshots::run($this->tenantSource, $sourceId);
        }

        return $mailshot;
    }

    public function parseProspect($sourceId): ?Prospect
    {
        if (!$sourceId) {
            return null;
        }

        $prospect = Prospect::where('source_id', $sourceId)->first();
        if (!$prospect) {
            $prospect = FetchProspects::run($this->tenantSource, $sourceId);
        }

        return $prospect;
    }

    public function parseDispatchedEmail($sourceId): ?DispatchedEmail
    {
        $dispatchedEmail = DispatchedEmail::where('source_id', $sourceId)->first();
        if (!$dispatchedEmail) {
            $dispatchedEmail = FetchDispatchedEmails::run($this->tenantSource, $sourceId);
        }

        return $dispatchedEmail;
    }

    public function parseWarehouse($sourceId): ?Warehouse
    {
        if (!$sourceId) {
            return null;
        }

        $warehouse = Warehouse::withTrashed()->where('source_id', $sourceId)->first();
        if (!$warehouse) {
            $warehouse = FetchWarehouses::run($this->tenantSource, $sourceId);
        }

        return $warehouse;
    }

    public function parsePayment($sourceId): Payment
    {
        $payment = Payment::withTrashed()->where('source_id', $sourceId)->first();
        if (!$payment) {
            $payment = FetchPayments::run($this->tenantSource, $sourceId);
        }

        return $payment;
    }

    public function parseTradeUnit($sourceId): TradeUnit
    {
        $tradeUnit = TradeUnit::withTrashed()->where('source_id', $sourceId)->first();
        if (!$tradeUnit) {
            $tradeUnit = FetchTradeUnits::run($this->tenantSource, $sourceId);
        }

        return $tradeUnit;
    }
}
