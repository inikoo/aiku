<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:52:35 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers;

use App\Models\Accounting\Invoice;
use App\Transfers\Aurora\FetchAuroraCustomer;
use App\Transfers\Aurora\FetchAuroraProspect;
use App\Transfers\Aurora\FetchAuroraShop;
use App\Transfers\Aurora\FetchAuroraWebpage;
use App\Transfers\Aurora\FetchAuroraWebsite;
use App\Transfers\Aurora\FetchAuroraWebUser;
use App\Transfers\Wowsbar\FetchWowsbarEmployee;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Helpers\Fetch;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WowsbarOrganisationService implements SourceOrganisationService
{
    public Organisation $organisation;
    public ?Fetch $fetch = null;

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

    public function fetchInvoiceTransaction($id, Invoice $invoice, bool $isFulfilment): ?array
    {
        return null;
    }

    public function fetchHistoricAsset($id): ?array
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

    public function fetchVariant($id): ?array
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

    public function fetchStockDelivery($id): ?array
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

    public function fetchClockingMachine($id): ?array
    {
        return null;
    }

    public function fetchArtefact($id): ?array
    {
        return null;
    }

    public function fetchBarcode($id): ?array
    {
        return null;
    }

}
