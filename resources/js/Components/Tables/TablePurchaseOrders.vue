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
        case 'grp.procurement.purchase-orders.index':
            return route(
                'grp.procurement.purchase-orders.show',
                [purchaseOrder.slug]);
        case 'grp.procurement.agents.show':
            return route(
                'grp.procurement.purchase-orders.show',
                [purchaseOrder.slug]);
    }
}

const formatDate = (dateIso: Date) => {
  const date = new Date(dateIso)
  const year = date.getFullYear()
  const month = (date.getMonth() + 1).toString().padStart(2, '0')
  const day = date.getDate().toString().padStart(2, '0')

  return `${year}-${month}-${day}`
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(number)="{ item: purchaseOrder }">
            <Link :href="PurchaseOrderRoute(purchaseOrder)">
                {{ purchaseOrder['number'] }}
            </Link>
        </template>
        <template #cell(date)="{ item: purchaseOrder }">
            {{ formatDate(purchaseOrder['date']) }}
        </template>
    </Table>
</template>


