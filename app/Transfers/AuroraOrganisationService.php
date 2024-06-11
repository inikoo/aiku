<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:52:35 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers;

use App\Models\Accounting\Invoice;
use App\Transfers\Aurora\FetchAuroraAgent;
use App\Transfers\Aurora\FetchAuroraArtefact;
use App\Transfers\Aurora\FetchAuroraBarcode;
use App\Transfers\Aurora\FetchAuroraClockingMachine;
use App\Transfers\Aurora\FetchAuroraCustomer;
use App\Transfers\Aurora\FetchAuroraCustomerClient;
use App\Transfers\Aurora\FetchAuroraDeletedCustomer;
use App\Transfers\Aurora\FetchAuroraDeletedEmployee;
use App\Transfers\Aurora\FetchAuroraDeletedInvoice;
use App\Transfers\Aurora\FetchAuroraDeletedLocation;
use App\Transfers\Aurora\FetchAuroraDeletedStock;
use App\Transfers\Aurora\FetchAuroraDeletedSupplier;
use App\Transfers\Aurora\FetchAuroraDeletedSupplierProduct;
use App\Transfers\Aurora\FetchAuroraDeliveryNote;
use App\Transfers\Aurora\FetchAuroraDeliveryNoteTransaction;
use App\Transfers\Aurora\FetchAuroraDepartment;
use App\Transfers\Aurora\FetchAuroraDispatchedEmail;
use App\Transfers\Aurora\FetchAuroraEmailTrackingEvent;
use App\Transfers\Aurora\FetchAuroraEmployee;
use App\Transfers\Aurora\FetchAuroraFamily;
use App\Transfers\Aurora\FetchAuroraHistoricAsset;
use App\Transfers\Aurora\FetchAuroraHistoricService;
use App\Transfers\Aurora\FetchAuroraInvoice;
use App\Transfers\Aurora\FetchAuroraInvoiceTransaction;
use App\Transfers\Aurora\FetchAuroraLocation;
use App\Transfers\Aurora\FetchAuroraLocationStocks;
use App\Transfers\Aurora\FetchAuroraMailshot;
use App\Transfers\Aurora\FetchAuroraOrder;
use App\Transfers\Aurora\FetchAuroraOrganisation;
use App\Transfers\Aurora\FetchAuroraOutbox;
use App\Transfers\Aurora\FetchAuroraPallet;
use App\Transfers\Aurora\FetchAuroraPayment;
use App\Transfers\Aurora\FetchAuroraPaymentAccount;
use App\Transfers\Aurora\FetchAuroraPaymentServiceProvider;
use App\Transfers\Aurora\FetchAuroraProduct;
use App\Transfers\Aurora\FetchAuroraProductStocks;
use App\Transfers\Aurora\FetchAuroraProspect;
use App\Transfers\Aurora\FetchAuroraPurchaseOrder;
use App\Transfers\Aurora\FetchAuroraService;
use App\Transfers\Aurora\FetchAuroraShipper;
use App\Transfers\Aurora\FetchAuroraShop;
use App\Transfers\Aurora\FetchAuroraStock;
use App\Transfers\Aurora\FetchAuroraStockDelivery;
use App\Transfers\Aurora\FetchAuroraStockFamily;
use App\Transfers\Aurora\FetchAuroraSupplier;
use App\Transfers\Aurora\FetchAuroraSupplierProduct;
use App\Transfers\Aurora\FetchAuroraTimesheet;
use App\Transfers\Aurora\FetchAuroraTradeUnit;
use App\Transfers\Aurora\FetchAuroraTradeUnitImages;
use App\Transfers\Aurora\FetchAuroraTransaction;
use App\Transfers\Aurora\FetchAuroraVariant;
use App\Transfers\Aurora\FetchAuroraWarehouse;
use App\Transfers\Aurora\FetchAuroraWarehouseArea;
use App\Transfers\Aurora\FetchAuroraWebpage;
use App\Transfers\Aurora\FetchAuroraWebsite;
use App\Transfers\Aurora\FetchAuroraWebUser;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Helpers\Fetch;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AuroraOrganisationService implements SourceOrganisationService
{
    public Organisation $organisation;
    public ?Fetch $fetch = null;

    public function initialisation(Organisation $organisation, string $databaseSuffix = ''): void
    {
        $database_settings = data_get(config('database.connections'), 'aurora');
        data_set($database_settings, 'database', Arr::get($organisation->source, 'db_name').$databaseSuffix);
        config(['database.connections.aurora' => $database_settings]);
        DB::connection('aurora');
        DB::purge('aurora');

        $this->organisation = $organisation;
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function fetchOrganisation(Organisation $organisation): ?array
    {
        return (new FetchAuroraOrganisation($this))->fetch();
    }

    public function fetchEmployee($id): ?array
    {
        return (new FetchAuroraEmployee($this))->fetch($id);
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

    public function fetchDeletedStock($id): ?array
    {
        return (new FetchAuroraDeletedStock($this))->fetch($id);
    }

    public function fetchDeletedLocation($id): ?array
    {
        return (new FetchAuroraDeletedLocation($this))->fetch($id);
    }

    public function fetchDeletedInvoice($id): ?array
    {
        return (new FetchAuroraDeletedInvoice($this))->fetch($id);
    }

    public function fetchWebUser($id): ?array
    {
        return (new FetchAuroraWebUser($this))->fetch($id);
    }

    public function fetchOrder($id): ?array
    {
        return (new FetchAuroraOrder($this))->fetch($id);
    }

    public function fetchDeliveryNote($id): ?array
    {
        return (new FetchAuroraDeliveryNote($this))->fetch($id);
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

    public function fetchDeliveryNoteTransaction($id, DeliveryNote $deliveryNote): ?array
    {
        return (new FetchAuroraDeliveryNoteTransaction($this))->fetchDeliveryNoteTransaction($id, $deliveryNote);
    }

    public function fetchInvoiceTransaction($id, Invoice $invoice, bool $isFulfilment): ?array
    {
        return (new FetchAuroraInvoiceTransaction($this))->fetchInvoiceTransaction($id, $invoice, $isFulfilment);
    }

    public function fetchHistoricAsset($id): ?array
    {
        return (new FetchAuroraHistoricAsset($this))->fetch($id);
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

    public function fetchVariant($id): ?array
    {
        return (new FetchAuroraVariant($this))->fetch($id);
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

    public function fetchTradeUnitImages($id): ?array
    {
        return (new FetchAuroraTradeUnitImages($this))->fetch($id);
    }

    public function fetchLocationStocks($id): ?array
    {
        return (new FetchAuroraLocationStocks($this))->fetch($id);
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

    public function fetchDeletedSupplier($id): ?array
    {
        return (new FetchAuroraDeletedSupplier($this))->fetch($id);
    }

    public function fetchDeletedEmployee($id): ?array
    {
        return (new FetchAuroraDeletedEmployee($this))->fetch($id);
    }

    public function fetchDeletedSupplierProduct($id): ?array
    {
        return (new FetchAuroraDeletedSupplierProduct($this))->fetch($id);
    }

    public function fetchPaymentServiceProvider($id): ?array
    {
        return (new FetchAuroraPaymentServiceProvider($this))->fetch($id);
    }

    public function fetchPaymentAccount($id): ?array
    {
        return (new FetchAuroraPaymentAccount($this))->fetch($id);
    }

    public function fetchPayment($id): ?array
    {
        return (new FetchAuroraPayment($this))->fetch($id);
    }

    public function fetchOutbox($id): ?array
    {
        return (new FetchAuroraOutbox($this))->fetch($id);
    }

    public function fetchMailshot($id): ?array
    {
        return (new FetchAuroraMailshot($this))->fetch($id);
    }

    public function fetchDispatchedEmail($id): ?array
    {
        return (new FetchAuroraDispatchedEmail($this))->fetch($id);
    }

    public function fetchProspect($id): ?array
    {
        return (new FetchAuroraProspect($this))->fetch($id);
    }

    public function fetchEmailTrackingEvent($id): ?array
    {
        return (new FetchAuroraEmailTrackingEvent($this))->fetch($id);
    }

    public function fetchPurchaseOrder($id): ?array
    {
        return (new FetchAuroraPurchaseOrder($this))->fetch($id);
    }

    public function fetchStockDelivery($id): ?array
    {
        return (new FetchAuroraStockDelivery($this))->fetch($id);
    }

    public function fetchPallet($id): ?array
    {
        return (new FetchAuroraPallet($this))->fetch($id);
    }

    public function fetchTimesheet($id): ?array
    {
        return (new FetchAuroraTimesheet($this))->fetch($id);
    }

    public function fetchClockingMachine($id): ?array
    {
        return (new FetchAuroraClockingMachine($this))->fetch($id);
    }

    public function fetchArtefact($id): array
    {
        return (new FetchAuroraArtefact($this))->fetch($id);
    }

    public function fetchBarcode($id): array
    {
        return (new FetchAuroraBarcode($this))->fetch($id);
    }

}
