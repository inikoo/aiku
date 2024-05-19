<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {PurchaseOrder} from "@/types/purchase-order";

const props = defineProps<{
    data: object,
    tab?: string
}>()


function PurchaseOrderRoute(purchaseOrder: PurchaseOrder) {
    switch (route().current()) {
        case 'grp.org.procurement.purchase-orders.index':
            return route(
                'grp.org.procurement.purchase-orders.show',
                [purchaseOrder.slug]);
        case 'grp.org.procurement.agents.show':
            return route(
                'grp.org.procurement.purchase-orders.show',
                [purchaseOrder.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(number)="{ item: purchaseOrderItem }">
            <Link :href="PurchaseOrderRoute(purchaseOrderItem)">
                {{ purchaseOrderItem['number'] }}
            </Link>
        </template>
        <template #cell(name)="{ item: purchaseOrderItem }">
            {{ purchaseOrderItem['name'] }}
        </template>
        <template #cell(unit_price)="{ item: purchaseOrderItem }">
            {{ purchaseOrderItem['unit_price'] }}
        </template>
        <template #cell(unit_quantity)="{ item: purchaseOrderItem }">
            {{ purchaseOrderItem['unit_quantity'] }}
        </template>
        <template #cell(unit_cost)="{ item: purchaseOrderItem }">
            {{ purchaseOrderItem['unit_cost'] }}
        </template>
        <template #cell(status)="{ item: purchaseOrderItem }">
            {{ purchaseOrderItem['status'] }}
        </template>
    </Table>
</template>


