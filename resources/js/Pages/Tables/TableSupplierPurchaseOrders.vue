<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import SupplierPurchaseOrder from "@/Pages/Procurement/SupplierPurchaseOrders.vue";

const props = defineProps<{
    data: object
}>()


function supplierPurchaseOrderRoute(supplierPurchaseOrder: SupplierPurchaseOrder) {
    switch (route().current()) {
        case 'procurement.supplier-purchase-orders.index':
            return route(
                'procurement.purchase-orders.show',
                [supplierPurchaseOrder.slug]);
        case 'procurement.agents.show':
            return route(
                'procurement.agents.show.supplier-purchase-orders.show',
                [supplierPurchaseOrder.agent_slug, supplierPurchaseOrder.slug]);
        case 'procurement.agents.show.suppliers.show':
            return route(
                'procurement.agents.show.suppliers.show.supplier-purchase-orders.show',
                [supplierPurchaseOrder.agent_slug, supplierPurchaseOrder.supplier_slug, supplierPurchaseOrder.slug]);
        default:
            return route(
                'procurement.supplier-purchase-orders.show',
                [supplierPurchaseOrder.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'spd'" class="mt-5">
        <template #cell(number)="{ item: supplierPurchaseOrder }">
            <Link :href="supplierPurchaseOrderRoute(supplierPurchaseOrder)">
                {{ supplierPurchaseOrder['number'] }}
            </Link>
        </template>
    </Table>
</template>


