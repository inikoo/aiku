<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Apr 2024 00:03:30 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\Organisation;

use App\Models\Dispatch\DeliveryNote;
use App\Models\Helpers\Fetch;
use App\Models\SysAdmin\Organisation;
use App\Services\Organisation\Aurora\FetchAuroraCustomer;
use App\Services\Organisation\Aurora\FetchAuroraProspect;
use App\Services\Organisation\Aurora\FetchAuroraShop;
use App\Services\Organisation\Aurora\FetchAuroraWebpage;
use App\Services\Organisation\Aurora\FetchAuroraWebsite;
use App\Services\Organisation\Aurora\FetchAuroraWebUser;
use App\Services\Organisation\Wowsbar\FetchWowsbarEmployee;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WowsbarOrganisationService implements SourceOrganisationService
{
    public Organisation $organisation;
    public ?Fetch $fetch=null;

    public function initialisation(Organisation $organisation, string $databaseSuffix = ''): void
    {
        $database_settings = data_get(config('database.connections'), 'wowsbar');
        data_set($database_settings, 'database', Arr::get($organisation->source, 'db_name').$databaseSuffix);
        config(['database.connections.wowsbar' => $database_settings]);
        DB::connection('wowsbar');
        DB::purge('wowsbar');

        $this->organisation = $organisation;
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function fetchOrganisation(Organisation $organisation): ?array
    {
        return null;
    }

    public function fetchEmployee($id): ?array
    {
        return (new FetchWowsbarEmployee($this))->fetch($id);
    }

    public function fetchShop($id): ?array
    {
        return (new FetchAuroraShop($this))->fetch($id);
    }

    public function fetchWebsite($id): ?array
    {
        return (new FetchAuroraWebsite($this))->fetch($id);
    }

    public function fetchWebpage($id): ?array
    {
        return (new FetchAuroraWebpage($this))->fetch($id);
    }

    public function fetchShipper($id): ?array
    {
        return null;
    }

    public function fetchCustomer($id): ?array
    {
        return (new FetchAuroraCustomer($this))->fetch($id);
    }

    public function fetchDeletedCustomer($id): ?array
    {
        return null;
    }

    public function fetchDeletedStock($id): ?array
    {
        return null;
    }

    public function fetchDeletedLocation($id): ?array
    {
        return null;
    }

    public function fetchDeletedInvoice($id): ?array
    {
        return null;
    }

    public function fetchWebUser($id): ?array
    {
        return (new FetchAuroraWebUser($this))->fetch($id);
    }

    public function fetchOrder($id): ?array
    {
        return null;
    }

    public function fetchDeliveryNote($id): ?array
    {
        return null;
    }

    public function fetchInvoice($id): ?array
    {
        return null;
    }

    public function fetchCustomerClient($id): ?array
    {
        return null;
    }

    public function fetchWarehouse($id): ?array
    {
        return null;
    }

    public function fetchWarehouseArea($id): ?array
    {
        return null;
    }

    public function fetchLocation($id): ?array
    {
        return null;
    }

    public function fetchTransaction($id): ?array
    {
        return null;
    }

    public function fetchDeliveryNoteTransaction($id, DeliveryNote $deliveryNote): ?array
    {
        return null;
    }

    public function fetchInvoiceTransaction($id): ?array
    {
        return null;
    }

    public function fetchHistoricProduct($id): ?array
    {
        return null;
    }

    public function fetchHistoricService($id): ?array
    {
        return null;
    }

    public function fetchProductStocks($id): ?array
    {
        return null;
    }

    public function fetchDepartment($id): ?array
    {
        return null;
    }

    public function fetchFamily($id): ?array
    {
        return null;
    }

    public function fetchProduct($id): ?array
    {
        return null;
    }

    public function fetchOuter($id): ?array
    {
        return null;
    }

    public function fetchService($id): ?array
    {
        return null;
    }

    public function fetchStock($id): ?array
    {
        return null;
    }

    public function fetchStockFamily($id): ?array
    {
        return null;
    }

    public function fetchTradeUnit($id): ?array
    {
        return null;
    }

    public function fetchTradeUnitImages($id): ?array
    {
        return null;
    }

    public function fetchLocationStocks($id): ?array
    {
        return null;
    }

    public function fetchAgent($id): ?array
    {
        return null;
    }

    public function fetchSupplier($id): ?array
    {
        return null;
    }

    public function fetchSupplierProduct($id): ?array
    {
        return null;
    }

    public function fetchDeletedSupplier($id): ?array
    {
        return null;
    }

    public function fetchDeletedEmployee($id): ?array
    {
        return null;
    }

    public function fetchDeletedSupplierProduct($id): ?array
    {
        return null;
    }

    public function fetchPaymentServiceProvider($id): ?array
    {
        return null;
    }

    public function fetchPaymentAccount($id): ?array
    {
        return null;
    }

    public function fetchPayment($id): ?array
    {
        return null;
    }

    public function fetchOutbox($id): ?array
    {
        return null;
    }

    public function fetchMailshot($id): ?array
    {
        return null;

    }

    public function fetchDispatchedEmail($id): ?array
    {
        return null;

    }

    public function fetchProspect($id): ?array
    {
        return (new FetchAuroraProspect($this))->fetch($id);
    }

    public function fetchEmailTrackingEvent($id): ?array
    {
        return null;
    }

    public function fetchPurchaseOrder($id): ?array
    {
        return null;
    }

    public function fetchSupplierDelivery($id): ?array
    {
        return null;
    }

    public function fetchPallet($id): ?array
    {
        return null;
    }

    public function fetchTimesheet($id): ?array
    {
        return null;
    }

}
