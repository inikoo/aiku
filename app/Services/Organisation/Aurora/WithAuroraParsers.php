<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 15:57:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceFetch\Aurora\FetchAuroraCustomers;
use App\Actions\SourceFetch\Aurora\FetchAuroraDeletedCustomers;
use App\Actions\SourceFetch\Aurora\FetchAuroraDeletedEmployees;
use App\Actions\SourceFetch\Aurora\FetchAuroraDeletedStocks;
use App\Actions\SourceFetch\Aurora\FetchAuroraDepartments;
use App\Actions\SourceFetch\Aurora\FetchAuroraDispatchedEmails;
use App\Actions\SourceFetch\Aurora\FetchAuroraEmployees;
use App\Actions\SourceFetch\Aurora\FetchAuroraFamilies;
use App\Actions\SourceFetch\Aurora\FetchHistoricProducts;
use App\Actions\SourceFetch\Aurora\FetchHistoricServices;
use App\Actions\SourceFetch\Aurora\FetchAuroraLocations;
use App\Actions\SourceFetch\Aurora\FetchAuroraMailshots;
use App\Actions\SourceFetch\Aurora\FetchAuroraOrders;
use App\Actions\SourceFetch\Aurora\FetchAuroraOutboxes;
use App\Actions\SourceFetch\Aurora\FetchAuroraPaymentAccounts;
use App\Actions\SourceFetch\Aurora\FetchAuroraPayments;
use App\Actions\SourceFetch\Aurora\FetchAuroraPaymentServiceProviders;
use App\Actions\SourceFetch\Aurora\FetchAuroraProducts;
use App\Actions\SourceFetch\Aurora\FetchAuroraProspects;
use App\Actions\SourceFetch\Aurora\FetchAuroraServices;
use App\Actions\SourceFetch\Aurora\FetchAuroraShippers;
use App\Actions\SourceFetch\Aurora\FetchAuroraShops;
use App\Actions\SourceFetch\Aurora\FetchAuroraStocks;
use App\Actions\SourceFetch\Aurora\FetchAuroraTradeUnits;
use App\Actions\SourceFetch\Aurora\FetchAuroraWarehouses;
use App\Actions\SourceFetch\Aurora\FetchAuroraWebsites;
use App\Enums\Helpers\TaxNumber\TaxNumberStatusEnum;
use App\Enums\Market\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
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
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\Warehouse;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\Market\HistoricOuterable;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Models\OMS\Order;
use App\Models\OMS\Transaction;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\Supplier;
use App\Models\Web\Website;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait WithAuroraParsers
{
    protected function parseDate($value): ?string
    {
        return ($value != '' && $value != '0000-00-00 00:00:00' && $value != '2018-00-00 00:00:00') ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    protected function parseDatetime($value): ?Carbon
    {
        return ($value != '' && $value != '0000-00-00 00:00:00' && $value != '2018-00-00 00:00:00') ? Carbon::parse($value) : null;
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
            $sourceData = explode(':', $sourceId);
            $shop       = FetchAuroraShops::run($this->organisationSource, $sourceData[1]);
        }

        return $shop;
    }

    public function parseWebsite($sourceId): Website
    {
        $website = Website::where('source_id', $sourceId)->first();
        if (!$website) {
            $sourceData = explode(':', $sourceId);
            $website    = FetchAuroraWebsites::run($this->organisationSource, $sourceData[1]);
        }

        return $website;
    }


    public function parseHistoricProduct($sourceId): HistoricOuterable
    {
        $historicProduct = HistoricOuterable::where('source_id', $sourceId)->first();
        if (!$historicProduct) {
            $historicProduct = FetchHistoricProducts::run($this->organisationSource, $sourceId);
        }

        return $historicProduct;
    }


    public function parseHistoricItem($sourceId): HistoricOuterable
    {
        $auroraData = DB::connection('aurora')
            ->table('Product History Dimension as PH')
            ->leftJoin('Product Dimension as P', 'P.Product ID', 'PH.Product ID')
            ->select('Product Type')
            ->where('PH.Product Key', $sourceId)->first();

        $historicItem = HistoricOuterable::where('source_id', $sourceId)->first();

        if ($auroraData->{'Product Type'} == 'Product') {
            if (!$historicItem) {
                $historicItem = FetchHistoricProducts::run($this->organisationSource, $sourceId);
            }
        } else {
            if (!$historicItem) {
                $historicItem = FetchHistoricServices::run($this->organisationSource, $sourceId);
            }
        }

        return $historicItem;
    }

    public function parseProduct(string $sourceId): Product
    {
        $product = Product::where('source_id', $sourceId)->first();
        if (!$product) {
            $sourceData = explode(':', $sourceId);
            $product    = FetchAuroraProducts::run($this->organisationSource, $sourceData[1]);
        }

        return $product;
    }

    public function parseDepartment(string $sourceId): ?ProductCategory
    {
        $department = ProductCategory::where('type', ProductCategoryTypeEnum::DEPARTMENT)->where('source_department_id', $sourceId)->first();
        if (!$department) {
            $sourceData = explode(':', $sourceId);
            $department = FetchAuroraDepartments::run($this->organisationSource, $sourceData[1]);
        }

        return $department;
    }

    public function parseFamily(string $sourceId): ?ProductCategory
    {
        $family = ProductCategory::where('type', ProductCategoryTypeEnum::FAMILY)->where('source_family_id', $sourceId)->first();
        if (!$family) {
            $sourceData = explode(':', $sourceId);
            $family     = FetchAuroraFamilies::run($this->organisationSource, $sourceData[1]);
        }

        return $family;
    }

    public function parseService(string $sourceId): Product
    {
        $service = Product::withTrashed()->where('source_id', $sourceId)->first();
        if (!$service) {
            $sourceData = explode(':', $sourceId);
            $service    = FetchAuroraServices::run($this->organisationSource, $sourceData[1]);
        }

        return $service;
    }

    public function parseCustomer(string $sourceId): ?Customer
    {
        if (!$sourceId) {
            return null;
        }
        $customer = Customer::withTrashed()->where('source_id', $sourceId)->first();
        if (!$customer) {
            $sourceData = explode(':', $sourceId);
            $customer   = FetchAuroraCustomers::run($this->organisationSource, $sourceData[1]);
            if (!$customer) {
                $customer = FetchAuroraDeletedCustomers::run($this->organisationSource, $sourceData[1]);
            }
        }

        return $customer;
    }

    public function parseSupplier($sourceSlug): ?Supplier
    {
        return Supplier::withTrashed()->where('source_slug', $sourceSlug)->first();
    }

    public function parseAgent($sourceSlug): ?Agent
    {
        return Agent::withTrashed()->where('source_slug', $sourceSlug)->first();
    }

    public function parseOrgStock($sourceId): ?OrgStock
    {
        $orgStock   = OrgStock::withTrashed()->where('source_id', $sourceId)->first();
        $sourceData = explode(':', $sourceId);
        if (!$orgStock) {

            $res     = FetchAuroraStocks::run($this->organisationSource, $sourceData[1]);
            $orgStock=$res['orgStock'];
        }
        /*
        if (!$orgStock) {
            $res = FetchAuroraDeletedStocks::run($this->organisationSource,$sourceData[1]);
            $orgStock=$res['org_stock'];

        }
        */
        return $orgStock;
    }

    public function parseStock($sourceId): ?Stock
    {
        $stock      = Stock::withTrashed()->where('source_id', $sourceId)->first();
        $sourceData = explode(':', $sourceId);
        if (!$stock) {
            $res  = FetchAuroraStocks::run($this->organisationSource, $sourceData[1]);
            $stock=$res['stock'];
        }
        if (!$stock) {
            $res  = FetchAuroraDeletedStocks::run($this->organisationSource, $sourceData[1]);
            $stock=$res['stock'];
        }

        return $stock;
    }

    public function parseLocation($sourceId): ?Location
    {
        $location = Location::where('source_id', $sourceId)->first();
        if (!$location) {
            $sourceData = explode(':', $sourceId);
            $location   = FetchAuroraLocations::run($this->organisationSource, $sourceData[1]);
        }

        return $location;
    }

    public function parseOrder(?int $sourceId): ?Order
    {
        if (!$sourceId) {
            return null;
        }

        $order = Order::where('source_id', $sourceId)->first();
        if (!$order) {
            $order = FetchAuroraOrders::run($this->organisationSource, $sourceId);
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
            $shipper = FetchAuroraShippers::run($this->organisationSource, $sourceId);
        }

        return $shipper;
    }

    public function parseOrgPaymentServiceProvider($sourceId): OrgPaymentServiceProvider
    {
        $orgPaymentServiceProvider = OrgPaymentServiceProvider::where('source_id', $sourceId)->first();
        if (!$orgPaymentServiceProvider) {
            $sourceData                = explode(':', $sourceId);
            $orgPaymentServiceProvider = FetchAuroraPaymentServiceProviders::run($this->organisationSource, $sourceData[1]);
        }

        return $orgPaymentServiceProvider;
    }

    public function parsePaymentAccount($sourceId): ?PaymentAccount
    {
        $paymentAccount = PaymentAccount::where('source_id', $sourceId)->first();
        if (!$paymentAccount) {
            $sourceData     = explode(':', $sourceId);
            $paymentAccount = FetchAuroraPaymentAccounts::run($this->organisationSource, $sourceData[1]);
        }

        return $paymentAccount;
    }

    public function parseEmployee($sourceId): ?Employee
    {
        $employee = Employee::withTrashed()->where('source_id', $sourceId)->first();
        if (!$employee) {
            $employee = FetchAuroraEmployees::run($this->organisationSource, $sourceId);
        }
        if (!$employee) {
            $employee = FetchAuroraDeletedEmployees::run($this->organisationSource, $sourceId);
        }

        return $employee;
    }


    public function parseOutbox($sourceId): ?Outbox
    {
        $outbox = Outbox::where('source_id', $sourceId)->first();
        if (!$outbox) {
            $outbox = FetchAuroraOutboxes::run($this->organisationSource, $sourceId);
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
            $mailshot = FetchAuroraMailshots::run($this->organisationSource, $sourceId);
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
            $prospect = FetchAuroraProspects::run($this->organisationSource, $sourceId);
        }

        return $prospect;
    }

    public function parseDispatchedEmail($sourceId): ?DispatchedEmail
    {
        $dispatchedEmail = DispatchedEmail::where('source_id', $sourceId)->first();
        if (!$dispatchedEmail) {
            $dispatchedEmail = FetchAuroraDispatchedEmails::run($this->organisationSource, $sourceId);
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
            $sourceData = explode(':', $sourceId);
            $warehouse  = FetchAuroraWarehouses::run($this->organisationSource, $sourceData[1]);
        }

        return $warehouse;
    }

    public function parsePayment($sourceId): Payment
    {
        $payment = Payment::withTrashed()->where('source_id', $sourceId)->first();
        if (!$payment) {
            $payment = FetchAuroraPayments::run($this->organisationSource, $sourceId);
        }

        return $payment;
    }

    public function parseTradeUnit($sourceSlug, $sourceId): TradeUnit
    {
        $tradeUnit = TradeUnit::withTrashed()->where('source_slug', $sourceSlug)->first();
        if (!$tradeUnit) {
            $tradeUnit = FetchAuroraTradeUnits::run($this->organisationSource, $sourceId);
        }

        return $tradeUnit;
    }

    public function cleanTradeUnitReference(string $reference): string
    {
        $reference = str_replace('&', 'and', $reference);
        $reference = str_replace('/', '_', $reference);
        $reference = preg_replace('/\s/', '_', $reference);
        $reference = preg_replace('/\)$/', '', $reference);
        $reference = str_replace('(', '-', $reference);
        $reference = str_replace(')', '-', $reference);
        $reference = str_replace("'", '', $reference);
        $reference = str_replace(",", '', $reference);
        $reference = str_replace("/", '-', $reference);

        /** @noinspection PhpDuplicateArrayKeysInspection */
        /** @noinspection DuplicatedCode */
        $normalizeChars = array(
            'Š' => 'S',
            'š' => 's',
            'Ð' => 'Dj',
            'Ž' => 'Z',
            'ž' => 'z',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ń' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ń' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y',
            'ƒ' => 'f',
            'ă' => 'a',
            'î' => 'i',
            'â' => 'a',
            'ș' => 's',
            'ț' => 't',
            'Ă' => 'A',
            'Î' => 'I',
            'Â' => 'A',
            'Ș' => 'S',
            'Ț' => 'T',
            'č' => 'c'
        );

        $reference = str_replace('_-_', '-', $reference);
        $reference = str_replace('_+_', '-', $reference);
        $reference = strtr($reference, $normalizeChars);

        return str_replace('--', '-', $reference);
    }

    public function cleanWebpageCode($string): string
    {
        $string = str_replace(' ', '_', $string);
        $string = str_replace('/', '_', $string);
        $string = str_replace('&', '_', $string);
        $string = str_replace('(', '_', $string);
        $string = str_replace(')', '_', $string);
        $string = str_replace('!', '_', $string);
        $string = str_replace('?', '_', $string);


        /** @noinspection PhpDuplicateArrayKeysInspection */
        /** @noinspection DuplicatedCode */
        $normalizeChars = array(
            'Š' => 'S',
            'š' => 's',
            'Ð' => 'Dj',
            'Ž' => 'Z',
            'ž' => 'z',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ń' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ń' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y',
            'ƒ' => 'f',
            'ă' => 'a',
            'î' => 'i',
            'â' => 'a',
            'ș' => 's',
            'ț' => 't',
            'Ă' => 'A',
            'Î' => 'I',
            'Â' => 'A',
            'Ș' => 'S',
            'Ț' => 'T',
            'č' => 'c'
        );

        return strtr($string, $normalizeChars);
    }


}
