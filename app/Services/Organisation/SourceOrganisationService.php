<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:44:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation;

use App\Models\Dispatching\DeliveryNote;
use App\Models\SysAdmin\Organisation;

interface SourceOrganisationService
{
    public function fetchOrganisation(Organisation $organisation);

    public function fetchEmployee($id);

    public function fetchShop($id);

    public function fetchWebsite($id);

    public function fetchWebpage($id);

    public function fetchShipper($id);

    public function fetchTransaction($id);

    public function fetchDeliveryNoteTransaction($id, DeliveryNote $deliveryNote);

    public function fetchInvoiceTransaction($id);

    public function fetchCustomer($id);

    public function fetchDeletedCustomer($id);

    public function fetchDeletedStock($id);

    public function fetchDeletedLocation($id);

    public function fetchDeletedInvoice($id);

    public function fetchDeletedSupplier($id);

    public function fetchDeletedEmployee($id);

    public function fetchDeletedSupplierProduct($id);

    public function fetchCustomerClient($id);

    public function fetchWebUser($id);

    public function fetchOrder($id);

    public function fetchDeliveryNote($id);

    public function fetchInvoice($id);

    public function fetchWarehouse($id);

    public function fetchWarehouseArea($id);

    public function fetchLocation($id);

    public function fetchHistoricAsset($id);

    public function fetchHistoricService($id);

    public function fetchDepartment($id);

    public function fetchFamily($id);

    public function fetchProduct($id);

    public function fetchVariant($id);

    public function fetchService($id);

    public function fetchProductStocks($id);

    public function fetchStock($id);

    public function fetchStockFamily($id);

    public function fetchTradeUnit($id);

    public function fetchTradeUnitImages($id);

    public function fetchLocationStocks($id);

    public function fetchAgent($id);

    public function fetchSupplier($id);

    public function fetchSupplierProduct($id);

    public function fetchArtefact($id);

    public function fetchPaymentServiceProvider($id);

    public function fetchPaymentAccount($id);

    public function fetchPayment($id);

    public function fetchOutbox($id);

    public function fetchMailshot($id);

    public function fetchDispatchedEmail($id);

    public function fetchProspect($id);

    public function fetchEmailTrackingEvent($id);

    public function initialisation(Organisation $organisation);

    public function fetchPurchaseOrder($id);

    public function fetchStockDelivery($id);

    public function fetchPallet($id);

    public function fetchTimesheet($id);

    public function fetchClockingMachine($id);

    public function fetchBarcode($id);

}
