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
        case 'grp.org.procurement.purchase_orders.index':
            return route(
                'grp.org.procurement.purchase_orders.show',
                [purchaseOrder.slug]);
        case 'grp.org.procurement.agents.show':
            return route(
                'grp.org.procurement.purchase_orders.show',
                [purchaseOrder.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: purchaseOrderTransaction }">
            <Link :href="PurchaseOrderRoute(purchaseOrderTransaction)">
                {{ purchaseOrderTransaction['reference'] }}
            </Link>
        </template>
        <template #cell(name)="{ item: purchaseOrderTransaction }">
            {{ purchaseOrderTransaction['name'] }}
        </template>
        <template #cell(unit_price)="{ item: purchaseOrderTransaction }">
            {{ purchaseOrderTransaction['unit_price'] }}
        </template>
        <template #cell(unit_quantity)="{ item: purchaseOrderTransaction }">
            {{ purchaseOrderTransaction['unit_quantity'] }}
        </template>
        <template #cell(unit_cost)="{ item: purchaseOrderTransaction }">
            {{ purchaseOrderTransaction['unit_cost'] }}
        </template>
        <template #cell(status)="{ item: purchaseOrderTransaction }">
            {{ purchaseOrderTransaction['status'] }}
        </template>
    </Table>
</template>


