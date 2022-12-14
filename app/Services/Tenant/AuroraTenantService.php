<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:46:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Tenant;


use App\Models\Central\Tenant;
use App\Services\Tenant\Aurora\FetchAuroraAgent;
use App\Services\Tenant\Aurora\FetchAuroraCustomer;
use App\Services\Tenant\Aurora\FetchAuroraCustomerClient;
use App\Services\Tenant\Aurora\FetchAuroraDeletedCustomer;
use App\Services\Tenant\Aurora\FetchAuroraDepartment;
use App\Services\Tenant\Aurora\FetchAuroraEmployee;
use App\Services\Tenant\Aurora\FetchAuroraFamily;
use App\Services\Tenant\Aurora\FetchAuroraGuest;
use App\Services\Tenant\Aurora\FetchAuroraHistoricProduct;
use App\Services\Tenant\Aurora\FetchAuroraHistoricService;
use App\Services\Tenant\Aurora\FetchAuroraInvoice;
use App\Services\Tenant\Aurora\FetchAuroraInvoiceTransaction;
use App\Services\Tenant\Aurora\FetchAuroraLocation;
use App\Services\Tenant\Aurora\FetchAuroraOrder;
use App\Services\Tenant\Aurora\FetchAuroraProduct;
use App\Services\Tenant\Aurora\FetchAuroraProductStocks;
use App\Services\Tenant\Aurora\FetchAuroraService;
use App\Services\Tenant\Aurora\FetchAuroraShipper;
use App\Services\Tenant\Aurora\FetchAuroraShop;
use App\Services\Tenant\Aurora\FetchAuroraStock;
use App\Services\Tenant\Aurora\FetchAuroraStockFamily;
use App\Services\Tenant\Aurora\FetchAuroraStockLocations;
use App\Services\Tenant\Aurora\FetchAuroraSupplierProduct;
use App\Services\Tenant\Aurora\FetchAuroraTradeUnit;
use App\Services\Tenant\Aurora\FetchAuroraTransaction;
use App\Services\Tenant\Aurora\FetchAuroraUser;
use App\Services\Tenant\Aurora\FetchAuroraWarehouse;
use App\Services\Tenant\Aurora\FetchAuroraWarehouseArea;
use App\Services\Tenant\Aurora\FetchAuroraWebsite;
use App\Services\Tenant\Aurora\FetchAuroraSupplier;

use App\Services\Tenant\Aurora\FetchAuroraWebUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * @property Tenant $tenant
 */
class AuroraTenantService implements SourceTenantService
{

    public function initialisation(Tenant $tenant)
    {
        $database_settings = data_get(config('database.connections'), 'aurora');
        data_set($database_settings, 'database', Arr::get($tenant->source, 'db_name'));
        config(['database.connections.aurora' => $database_settings]);
        DB::connection('aurora');
        DB::purge('aurora');

        $this->tenant = $tenant;
    }

    public function fetchUser($id): ?array
    {
        return (new FetchAuroraUser($this))->fetch($id);
    }

    public function fetchEmployee($id): ?array
    {
        return (new FetchAuroraEmployee($this))->fetch($id);
    }

    public function fetchGuest($id): ?array
    {
        return (new FetchAuroraGuest($this))->fetch($id);
    }

    public function fetchShop($id): ?array
    {
        return (new FetchAuroraShop($this))->fetch($id);
    }

    public function fetchWebsite($id): ?array
    {
        return (new FetchAuroraWebsite($this))->fetch($id);
    }

    public function fetchShipper($id): ?array
    {
        return (new FetchAuroraShipper($this))->fetch($id);
    }

    public function fetchCustomer($id): ?array
    {
        return (new FetchAuroraCustomer($this))->fetch($id);
    }

    public function fetchDeletedCustomer($id): ?array
    {
        return (new FetchAuroraDeletedCustomer($this))->fetch($id);
    }

    public function fetchWebUser($id): ?array
    {
        return (new FetchAuroraWebUser($this))->fetch($id);
    }

    public function fetchOrder($id): ?array
    {
        return (new FetchAuroraOrder($this))->fetch($id);
    }

    public function fetchInvoice($id): ?array
    {
        return (new FetchAuroraInvoice($this))->fetch($id);
    }

    public function fetchCustomerClient($id): ?array
    {
        return (new FetchAuroraCustomerClient($this))->fetch($id);
    }

    public function fetchWarehouse($id): ?array
    {
        return (new FetchAuroraWarehouse($this))->fetch($id);
    }

    public function fetchWarehouseArea($id): ?array
    {
        return (new FetchAuroraWarehouseArea($this))->fetch($id);
    }

    public function fetchLocation($id): ?array
    {
        return (new FetchAuroraLocation($this))->fetch($id);
    }

    public function fetchTransaction($id): ?array
    {
        return (new FetchAuroraTransaction($this))->fetch($id);
    }

    public function fetchInvoiceTransaction($id): ?array
    {
        return (new FetchAuroraInvoiceTransaction($this))->fetch($id);
    }

    public function fetchHistoricProduct($id): ?array
    {
        return (new FetchAuroraHistoricProduct($this))->fetch($id);
    }

    public function fetchHistoricService($id): ?array
    {
        return (new FetchAuroraHistoricService($this))->fetch($id);
    }

    public function fetchProductStocks($id): ?array
    {
        return (new FetchAuroraProductStocks($this))->fetch($id);
    }

    public function fetchDepartment($id): ?array
    {
        return (new FetchAuroraDepartment($this))->fetch($id);
    }

    public function fetchFamily($id): ?array
    {
        return (new FetchAuroraFamily($this))->fetch($id);
    }

    public function fetchProduct($id): ?array
    {
        return (new FetchAuroraProduct($this))->fetch($id);
    }

    public function fetchService($id): ?array
    {
        return (new FetchAuroraService($this))->fetch($id);
    }

    public function fetchStock($id): ?array
    {
        return (new FetchAuroraStock($this))->fetch($id);
    }

    public function fetchStockFamily($id): ?array
    {
        return (new FetchAuroraStockFamily($this))->fetch($id);
    }

    public function fetchTradeUnit($id): ?array
    {
        return (new FetchAuroraTradeUnit($this))->fetch($id);
    }

    public function fetchStockLocations($id): ?array
    {
        return (new FetchAuroraStockLocations($this))->fetch($id);
    }

    public function fetchAgent($id): ?array
    {
        return (new FetchAuroraAgent($this))->fetch($id);
    }

    public function fetchSupplier($id): ?array
    {
        return (new FetchAuroraSupplier($this))->fetch($id);
    }

    public function fetchSupplierProduct($id): ?array
    {
        return (new FetchAuroraSupplierProduct($this))->fetch($id);
    }
}
