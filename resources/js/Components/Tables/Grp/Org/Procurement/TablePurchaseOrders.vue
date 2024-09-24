<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:49:03 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { PurchaseOrder } from "@/types/purchase-order"
import { useFormatTime } from '@/Composables/useFormatTime'
import Icon from '@/Components/Icon.vue'

defineProps<{
    data: {}
    tab?: string
}>()

function PurchaseOrderRoute(purchaseOrder: PurchaseOrder) {
    switch (route().current()) {
        case 'grp.org.procurement.purchase_orders.index':
            return route(
                'grp.org.procurement.purchase_orders.show',
                [route().params['organisation'], purchaseOrder.slug])
        case 'grp.org.procurement.agents.show':
            return route(
                'grp.org.procurement.agents.show.purchase_orders.show',
                [route().params['organisation'], route().params['agent'], purchaseOrder.slug])
        default:
            return ''
    }
}

function SupplierRoute(purchaseOrder: PurchaseOrder) {
    switch (route().current()) {
        case 'grp.org.procurement.purchase_orders.index':
            return route(
                'grp.org.procurement.org_suppliers.show',
                [route().params['organisation'], purchaseOrder.parent_slug])
        // case 'grp.org.procurement.agents.show':
        //     return route(
        //         'grp.org.procurement.agents.show.purchase_orders.show',
        //         [route().params['organisation'], route().params['agent'], purchaseOrder.slug])
        default:
            return ''
    }
}

function AgentRoute(purchaseOrder: PurchaseOrder) {
    switch (route().current()) {
        case 'grp.org.procurement.purchase_orders.index':
            return route(
                'grp.org.procurement.org_agents.show',
                [route().params['organisation'], purchaseOrder.parent_slug])
        // case 'grp.org.procurement.agents.show':
        //     return route(
        //         'grp.org.procurement.agents.show.purchase_orders.show',
        //         [route().params['organisation'], route().params['agent'], purchaseOrder.slug])
        default:
            return ''
    }
}


</script>

<template>
    <!-- <pre>{{ data.data }}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: purchaseOrder }">
            <Link :href="PurchaseOrderRoute(purchaseOrder)" class="primaryLink">
                {{ purchaseOrder.reference }}
            </Link>
        </template>

        <template #cell(parent_name)="{ item: purchaseOrder }">
            <Link :href="purchaseOrder.parent_type === 'OrgSupplier' ? SupplierRoute(purchaseOrder) : AgentRoute(purchaseOrder)" class="secondaryLink">
                {{ purchaseOrder.parent_name }}
            </Link>
        </template>
        
        <template #cell(state)="{ item: purchaseOrder }">
            <!-- <pre>{{ purchaseOrder }}</pre> -->
            <Icon :data="purchaseOrder.state_icon" />
        </template>
        
        <template #cell(date)="{ item: purchaseOrder }">
            {{ useFormatTime(purchaseOrder['date']) }}
        </template>

        <template #cell(parent)="{ item: purchaseOrder }">
            {{ purchaseOrder['parent_name'] }}
        </template>
    </Table>
</template>
