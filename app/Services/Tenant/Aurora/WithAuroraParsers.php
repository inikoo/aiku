<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 15:57:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Tenant\Aurora;

use App\Actions\SourceFetch\Aurora\FetchCustomers;
use App\Actions\SourceFetch\Aurora\FetchHistoricProducts;
use App\Actions\SourceFetch\Aurora\FetchHistoricServices;
use App\Actions\SourceFetch\Aurora\FetchLocations;
use App\Actions\SourceFetch\Aurora\FetchProducts;
use App\Actions\SourceFetch\Aurora\FetchShops;
use App\Actions\SourceFetch\Aurora\FetchStocks;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Inventory\Location;
use App\Models\Inventory\Stock;
use App\Models\Marketing\HistoricProduct;
use App\Models\Marketing\HistoricService;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait WithAuroraParsers
{

    protected function parseDate($value): ?string
    {
        return ($value != '' && $value != '0000-00-00 00:00:00'
            && $value != '2018-00-00 00:00:00') ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    protected function parseLanguageID($locale): int|null
    {
        if ($locale != '') {
            try {
                return Language::where(
                    'code',
                    match ($locale) {
                        'zh_CN.UTF-8' => 'zh-CN',
                        default => substr($locale, 0, 2)
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
        $addressData                        = [];
        $addressData['address_line_1']      = (string)Str::of($auAddressData->{$prefix.' Address Line 1'} ?? null)->limit(251);
        $addressData['address_line_2']      = (string)Str::of($auAddressData->{$prefix.' Address Line 2'} ?? null)->limit(251);
        $addressData['sorting_code']        = (string)Str::of($auAddressData->{$prefix.' Address Sorting Code'} ?? null)->limit(187);
        $addressData['postal_code']         = (string)Str::of($auAddressData->{$prefix.' Address Postal Code'} ?? null)->limit(187);
        $addressData['locality']            = (string)Str::of($auAddressData->{$prefix.' Address Locality'} ?? null)->limit(187);
        $addressData['dependant_locality']  = (string)Str::of($auAddressData->{$prefix.' Address Dependent Locality'} ?? null)->limit(187);
        $addressData['administrative_area'] = (string)Str::of($auAddressData->{$prefix.' Address Administrative Area'} ?? null)->limit(187);
        $addressData['country_id']          = $this->parseCountryID($auAddressData->{$prefix.' Address Country 2 Alpha Code'} ?? null, $prefix);

        return $addressData;
    }


    function parseShop($source_id): Shop
    {
        $shop = Shop::where('source_id', $source_id)->first();
        if (!$shop) {
            $shop = FetchShops::run($this->tenantSource, $source_id);
        }

        return $shop;
    }


    function parseHistoricProduct($source_id): HistoricProduct
    {
        $historicProduct = HistoricProduct::where('source_id', $source_id)->first();
        if (!$historicProduct) {
            $historicProduct = FetchHistoricProducts::run($this->tenantSource, $source_id);
        }

        return $historicProduct;
    }

    function parseHistoricService($source_id): HistoricService
    {
        $historicService = HistoricService::where('source_id', $source_id)->first();
        if (!$historicService) {
            $historicService = FetchHistoricServices::run($this->tenantSource, $source_id);
        }

        return $historicService;
    }


    function parseHistoricItem($source_id): HistoricProduct|HistoricService
    {

        $auroraData=DB::connection('aurora')
            ->table('Product History Dimension as PH')
            ->leftJoin('Product Dimension as P','P.Product ID','PH.Product ID')
            ->select('Product Type')
            ->where('PH.Product Key',$source_id)->first();

        if($auroraData->{'Product Type'}=='Product'){
            $historicItem = HistoricProduct::where('source_id', $source_id)->first();
            if (!$historicItem) {
                $historicItem = FetchHistoricProducts::run($this->tenantSource, $source_id);
            }

        }else{
            $historicItem = HistoricService::where('source_id', $source_id)->first();
            if (!$historicItem) {
                $historicItem = FetchHistoricServices::run($this->tenantSource, $source_id);
            }
        }
        return $historicItem;

    }

    function parseProduct($source_id): Product
    {
        $product = Product::where('source_id', $source_id)->first();
        if (!$product) {
            $product = FetchProducts::run($this->tenantSource, $source_id);
        }

        return $product;
    }

    function parseCustomer($source_id): Customer
    {
        $customer = Customer::where('source_id', $source_id)->first();
        if (!$customer) {
            $customer = FetchCustomers::run($this->tenantSource, $source_id);
        }
        return $customer;
    }

    function parseStock($source_id): ?Stock
    {
        $stock = Stock::where('source_id', $source_id)->first();
        if (!$stock) {
            $stock = FetchStocks::run($this->tenantSource, $source_id);
        }
        return $stock;
    }

    function parseLocation($source_id): Location
    {
        $location = Location::where('source_id', $source_id)->first();
        if (!$location) {
            $location = FetchLocations::run($this->tenantSource, $source_id);
        }
        return $location;
    }


}
