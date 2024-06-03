<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:46:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation;

use App\Models\Dispatch\DeliveryNote;
use App\Models\Helpers\Fetch;
use App\Models\SysAdmin\Organisation;
use App\Services\Organisation\Aurora\FetchAuroraAgent;
use App\Services\Organisation\Aurora\FetchAuroraArtefact;
use App\Services\Organisation\Aurora\FetchAuroraBarcode;
use App\Services\Organisation\Aurora\FetchAuroraClockingMachine;
use App\Services\Organisation\Aurora\FetchAuroraCustomer;
use App\Services\Organisation\Aurora\FetchAuroraCustomerClient;
use App\Services\Organisation\Aurora\FetchAuroraDeletedCustomer;
use App\Services\Organisation\Aurora\FetchAuroraDeletedEmployee;
use App\Services\Organisation\Aurora\FetchAuroraDeletedInvoice;
use App\Services\Organisation\Aurora\FetchAuroraDeletedLocation;
use App\Services\Organisation\Aurora\FetchAuroraDeletedStock;
use App\Services\Organisation\Aurora\FetchAuroraDeletedSupplier;
use App\Services\Organisation\Aurora\FetchAuroraDeletedSupplierProduct;
use App\Services\Organisation\Aurora\FetchAuroraDeliveryNote;
use App\Services\Organisation\Aurora\FetchAuroraDeliveryNoteTransaction;
use App\Services\Organisation\Aurora\FetchAuroraDepartment;
use App\Services\Organisation\Aurora\FetchAuroraDispatchedEmail;
use App\Services\Organisation\Aurora\FetchAuroraEmailTrackingEvent;
use App\Services\Organisation\Aurora\FetchAuroraEmployee;
use App\Services\Organisation\Aurora\FetchAuroraFamily;
use App\Services\Organisation\Aurora\FetchAuroraHistoricAsset;
use App\Services\Organisation\Aurora\FetchAuroraHistoricService;
use App\Services\Organisation\Aurora\FetchAuroraInvoice;
use App\Services\Organisation\Aurora\FetchAuroraInvoiceTransaction;
use App\Services\Organisation\Aurora\FetchAuroraLocation;
use App\Services\Organisation\Aurora\FetchAuroraLocationStocks;
use App\Services\Organisation\Aurora\FetchAuroraMailshot;
use App\Services\Organisation\Aurora\FetchAuroraOrder;
use App\Services\Organisation\Aurora\FetchAuroraOutbox;
use App\Services\Organisation\Aurora\FetchAuroraVariant;
use App\Services\Organisation\Aurora\FetchAuroraPayment;
use App\Services\Organisation\Aurora\FetchAuroraPaymentAccount;
use App\Services\Organisation\Aurora\FetchAuroraPaymentServiceProvider;
use App\Services\Organisation\Aurora\FetchAuroraProduct;
use App\Services\Organisation\Aurora\FetchAuroraProductStocks;
use App\Services\Organisation\Aurora\FetchAuroraProspect;
use App\Services\Organisation\Aurora\FetchAuroraPurchaseOrder;
use App\Services\Organisation\Aurora\FetchAuroraService;
use App\Services\Organisation\Aurora\FetchAuroraShipper;
use App\Services\Organisation\Aurora\FetchAuroraShop;
use App\Services\Organisation\Aurora\FetchAuroraStock;
use App\Services\Organisation\Aurora\FetchAuroraStockFamily;
use App\Services\Organisation\Aurora\FetchAuroraPallet;
use App\Services\Organisation\Aurora\FetchAuroraSupplier;
use App\Services\Organisation\Aurora\FetchAuroraStockDelivery;
use App\Services\Organisation\Aurora\FetchAuroraSupplierProduct;
use App\Services\Organisation\Aurora\FetchAuroraOrganisation;
use App\Services\Organisation\Aurora\FetchAuroraTimesheet;
use App\Services\Organisation\Aurora\FetchAuroraTradeUnit;
use App\Services\Organisation\Aurora\FetchAuroraTradeUnitImages;
use App\Services\Organisation\Aurora\FetchAuroraTransaction;
use App\Services\Organisation\Aurora\FetchAuroraWarehouse;
use App\Services\Organisation\Aurora\FetchAuroraWarehouseArea;
use App\Services\Organisation\Aurora\FetchAuroraWebpage;
use App\Services\Organisation\Aurora\FetchAuroraWebsite;
use App\Services\Organisation\Aurora\FetchAuroraWebUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AuroraOrganisationService implements SourceOrganisationService
{
    public Organisation $organisation;
    public ?Fetch $fetch=null;

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

    public function fetchInvoiceTransaction($id): ?array
    {
        return (new FetchAuroraInvoiceTransaction($this))->fetch($id);
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
