<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 15:57:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Tenant\Aurora;

use App\Actions\SourceFetch\Aurora\FetchAgents;
use App\Actions\SourceFetch\Aurora\FetchCustomers;
use App\Actions\SourceFetch\Aurora\FetchDeletedCustomers;
use App\Actions\SourceFetch\Aurora\FetchDeletedEmployees;
use App\Actions\SourceFetch\Aurora\FetchDeletedGuests;
use App\Actions\SourceFetch\Aurora\FetchDeletedStocks;
use App\Actions\SourceFetch\Aurora\FetchDeletedSuppliers;
use App\Actions\SourceFetch\Aurora\FetchDispatchedEmails;
use App\Actions\SourceFetch\Aurora\FetchEmployees;
use App\Actions\SourceFetch\Aurora\FetchGuests;
use App\Actions\SourceFetch\Aurora\FetchHistoricProducts;
use App\Actions\SourceFetch\Aurora\FetchHistoricServices;
use App\Actions\SourceFetch\Aurora\FetchLocations;
use App\Actions\SourceFetch\Aurora\FetchMailshots;
use App\Actions\SourceFetch\Aurora\FetchOrders;
use App\Actions\SourceFetch\Aurora\FetchOutboxes;
use App\Actions\SourceFetch\Aurora\FetchPaymentAccounts;
use App\Actions\SourceFetch\Aurora\FetchPaymentServiceProviders;
use App\Actions\SourceFetch\Aurora\FetchProducts;
use App\Actions\SourceFetch\Aurora\FetchProspects;
use App\Actions\SourceFetch\Aurora\FetchServices;
use App\Actions\SourceFetch\Aurora\FetchShippers;
use App\Actions\SourceFetch\Aurora\FetchShops;
use App\Actions\SourceFetch\Aurora\FetchStocks;
use App\Actions\SourceFetch\Aurora\FetchSuppliers;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Dispatch\Shipper;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Location;
use App\Models\Inventory\Stock;
use App\Models\Leads\Prospect;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\Marketing\HistoricProduct;
use App\Models\Marketing\HistoricService;
use App\Models\Marketing\Product;
use App\Models\Marketing\Service;
use App\Models\Marketing\Shop;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Sales\Transaction;
use App\Models\SysAdmin\Guest;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait WithAuroraParsers
{
    protected function parseDate($value): ?string
    {
        return ($value                                                                     != '' && $value != '0000-00-00 00:00:00'
                                                                                                 && $value  != '2018-00-00 00:00:00') ? Carbon::parse($value)->format('Y-m-d') : null;
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


    public function parseShop($source_id): Shop
    {
        $shop = Shop::where('source_id', $source_id)->first();
        if (!$shop) {
            $shop = FetchShops::run($this->tenantSource, $source_id);
        }

        return $shop;
    }


    public function parseHistoricProduct($source_id): HistoricProduct
    {
        $historicProduct = HistoricProduct::where('source_id', $source_id)->first();
        if (!$historicProduct) {
            $historicProduct = FetchHistoricProducts::run($this->tenantSource, $source_id);
        }

        return $historicProduct;
    }

    public function parseHistoricService($source_id): HistoricService
    {
        $historicService = HistoricService::where('source_id', $source_id)->first();
        if (!$historicService) {
            $historicService = FetchHistoricServices::run($this->tenantSource, $source_id);
        }

        return $historicService;
    }


    public function parseHistoricItem($source_id): HistoricProduct|HistoricService
    {
        $auroraData = DB::connection('aurora')
            ->table('Product History Dimension as PH')
            ->leftJoin('Product Dimension as P', 'P.Product ID', 'PH.Product ID')
            ->select('Product Type')
            ->where('PH.Product Key', $source_id)->first();

        if ($auroraData->{'Product Type'} == 'Product') {
            $historicItem = HistoricProduct::where('source_id', $source_id)->first();
            if (!$historicItem) {
                $historicItem = FetchHistoricProducts::run($this->tenantSource, $source_id);
            }
        } else {
            $historicItem = HistoricService::where('source_id', $source_id)->first();
            if (!$historicItem) {
                $historicItem = FetchHistoricServices::run($this->tenantSource, $source_id);
            }
        }

        return $historicItem;
    }

    public function parseProduct($source_id): Product
    {
        $product = Product::where('source_id', $source_id)->first();
        if (!$product) {
            $product = FetchProducts::run($this->tenantSource, $source_id);
        }

        return $product;
    }

    public function parseService($source_id): Service
    {
        $service = Service::where('source_id', $source_id)->first();
        if (!$service) {
            $service = FetchServices::run($this->tenantSource, $source_id);
        }

        return $service;
    }

    public function parseCustomer($source_id): ?Customer
    {
        if (!$source_id) {
            return null;
        }
        $customer = Customer::withTrashed()->where('source_id', $source_id)->first();
        if (!$customer) {
            $customer = FetchCustomers::run($this->tenantSource, $source_id);
            if (!$customer) {
                $customer = FetchDeletedCustomers::run($this->tenantSource, $source_id);
            }
        }

        return $customer;
    }

    public function parseSupplier($source_id): ?Supplier
    {
        $supplier = Supplier::withTrashed()->where('source_id', $source_id)->first();
        if (!$supplier) {
            $supplier = FetchSuppliers::run($this->tenantSource, $source_id);
            if (!$supplier) {
                $supplier = FetchDeletedSuppliers::run($this->tenantSource, $source_id);
            }
        }

        return $supplier;
    }

    public function parseAgent($source_id): ?Agent
    {
        $agent = Agent::where('source_id', $source_id)->first();
        if (!$agent) {
            $agent = FetchAgents::run($this->tenantSource, $source_id);
        }

        return $agent;
    }

    public function parseStock($source_id): ?Stock
    {
        $stock = Stock::withTrashed()->where('source_id', $source_id)->first();
        if (!$stock) {
            $stock = FetchStocks::run($this->tenantSource, $source_id);
        }
        if (!$stock) {
            $stock = FetchDeletedStocks::run($this->tenantSource, $source_id);
        }

        return $stock;
    }

    public function parseLocation($source_id): Location
    {
        $location = Location::where('source_id', $source_id)->first();
        if (!$location) {
            $location = FetchLocations::run($this->tenantSource, $source_id);
        }

        return $location;
    }

    public function parseOrder($source_id): ?Order
    {
        $order = Order::where('source_id', $source_id)->first();
        if (!$order) {
            $order = FetchOrders::run($this->tenantSource, $source_id);
        }

        return $order;
    }

    public function parseTransaction($source_id): ?Transaction
    {
        return Transaction::where('source_id', $source_id)->first();
    }

    public function parseShipper($source_id): Shipper
    {
        $shipper = Shipper::where('source_id', $source_id)->first();
        if (!$shipper) {
            $shipper = FetchShippers::run($this->tenantSource, $source_id);
        }

        return $shipper;
    }

    public function parsePaymentServiceProvider($source_id): PaymentServiceProvider
    {
        $paymentServiceProvider = PaymentServiceProvider::where('source_id', $source_id)->first();
        if (!$paymentServiceProvider) {
            $paymentServiceProvider = FetchPaymentServiceProviders::run($this->tenantSource, $source_id);
        }

        return $paymentServiceProvider;
    }

    public function parsePaymentAccount($source_id): PaymentAccount
    {
        $paymentAccount = PaymentAccount::where('source_id', $source_id)->first();
        if (!$paymentAccount) {
            $paymentAccount = FetchPaymentAccounts::run($this->tenantSource, $source_id);
        }

        return $paymentAccount;
    }

    public function parseEmployee($source_id): ?Employee
    {
        $employee = Employee::withTrashed()->where('source_id', $source_id)->first();
        if (!$employee) {
            $employee = FetchEmployees::run($this->tenantSource, $source_id);
        }
        if (!$employee) {
            $employee = FetchDeletedEmployees::run($this->tenantSource, $source_id);
        }

        return $employee;
    }

    public function parseGuest($source_id): ?Guest
    {
        $guest = Guest::withTrashed()->where('source_id', $source_id)->first();
        if (!$guest) {
            $guest = FetchGuests::run($this->tenantSource, $source_id);
        }
        if (!$guest) {
            $guest = FetchDeletedGuests::run($this->tenantSource, $source_id);
        }

        return $guest;
    }

    public function parseOutbox($source_id): ?Outbox
    {
        $outbox = Outbox::where('source_id', $source_id)->first();
        if (!$outbox) {
            $outbox = FetchOutboxes::run($this->tenantSource, $source_id);
        }

        return $outbox;
    }

    public function parseMailshot($source_id): ?Mailshot
    {
        if (!$source_id) {
            return null;
        }

        $mailshot = Mailshot::where('source_id', $source_id)->first();
        if (!$mailshot) {
            $mailshot = FetchMailshots::run($this->tenantSource, $source_id);
        }

        return $mailshot;
    }

    public function parseProspect($source_id): ?Prospect
    {
        if (!$source_id) {
            return null;
        }

        $prospect = Prospect::where('source_id', $source_id)->first();
        if (!$prospect) {
            $prospect = FetchProspects::run($this->tenantSource, $source_id);
        }

        return $prospect;
    }

    public function parseDispatchedEmail($source_id): ?DispatchedEmail
    {
        $dispatchedEmail = DispatchedEmail::where('source_id', $source_id)->first();
        if (!$dispatchedEmail) {
            $dispatchedEmail = FetchDispatchedEmails::run($this->tenantSource, $source_id);
        }
        return $dispatchedEmail;
    }
}
