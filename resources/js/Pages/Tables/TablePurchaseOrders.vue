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
    data: object
}>()


function PurchaseOrderRoute(purchaseOrder: PurchaseOrder) {
    switch (route().current()) {
        case 'procurement.purchase-orders.index':
            return route(
                'procurement.purchase-orders.show',
                [purchaseOrder.slug]);
        case 'procurement.agents.show':
            return route(
                'procurement.purchase-orders.show',
                [purchaseOrder.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'po'" class="mt-5">
        <template #cell(number)="{ item: purchaseOrder }">
            <Link :href="PurchaseOrderRoute(purchaseOrder)">
                {{ purchaseOrder['number'] }}
            </Link>
        </template>
    </Table>
</template>


