<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:49:03 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {PurchaseOrder} from "@/types/purchase-order";

defineProps<{
    data: object,
    tab?: string
}>()

function PurchaseOrderRoute(purchaseOrder: PurchaseOrder) {
    switch (route().current()) {
        case 'grp.org.procurement.purchase_orders.index':
            return route(
                'grp.org.procurement.purchase_orders.show',
                [route().params['organisation'],purchaseOrder.slug]);
        case 'grp.org.procurement.agents.show':
            return route(
                'grp.org.procurement.agents.show.purchase_orders.show',
                [route().params['organisation'],route().params['agent'],purchaseOrder.slug]);
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
      <template #cell(parent)="{ item: purchaseOrder }">
        {{ purchaseOrder['parent_name']}}
      </template>
    </Table>
</template>


