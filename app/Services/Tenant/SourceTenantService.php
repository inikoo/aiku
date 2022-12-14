<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:44:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Tenant;


use App\Models\Central\Tenant;

interface SourceTenantService
{
    public function fetchUser($id);

    public function fetchEmployee($id);

    public function fetchGuest($id);

    public function fetchShop($id);

    public function fetchWebsite($id);

    public function fetchShipper($id);

    public function fetchTransaction($id);

    public function fetchInvoiceTransaction($id);

    public function fetchCustomer($id);

    public function fetchDeletedCustomer($id);

    public function fetchCustomerClient($id);

    public function fetchWebUser($id);

    public function fetchOrder($id);

    public function fetchInvoice($id);

    public function fetchWarehouse($id);

    public function fetchWarehouseArea($id);

    public function fetchLocation($id);

    public function fetchHistoricProduct($id);

    public function fetchHistoricService($id);

    public function fetchDepartment($id);

    public function fetchFamily($id);

    public function fetchProduct($id);

    public function fetchService($id);

    public function fetchProductStocks($id);

    public function fetchStock($id);

    public function fetchStockFamily($id);

    public function fetchTradeUnit($id);

    public function fetchStockLocations($id);

    public function fetchAgent($id);

    public function fetchSupplier($id);

    public function fetchSupplierProduct($id);

    public function initialisation(Tenant $tenant);
}

