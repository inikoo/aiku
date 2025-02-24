<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 May 2024 08:59:19 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { Agent } from "@/types/agent"
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue"

defineProps<{
    data: {}
    tab?: string
}>()

console.log(route().current())
function agentRoute(agent: Agent) {
    switch (route().current()) {
        case "grp.org.procurement.org_agents.index":
            return route(
                "grp.org.procurement.org_agents.show",
                [route().params["organisation"], agent.slug])
        
        default:
            return ''
    }
}

function suppliersRoute(agent: Agent) {
    switch (route().current()) {
        case "grp.org.procurement.org_agents.index":
            return route(
                "grp.org.procurement.org_agents.show.suppliers.index",
                [route().params["organisation"], agent.slug])
        
        default:
            return ''
    }
}

function productsRoute(agent: Agent) {
    switch (route().current()) {
        case "grp.org.procurement.org_agents.index":
            return route(
                "grp.org.procurement.org_agents.show.supplier_products.index",
                [route().params["organisation"], agent.slug])
        
        default:
            return ''
    }
}

function purchaseOrdersRoute(agent: Agent) {
    switch (route().current()) {
        case "grp.org.procurement.org_agents.index":
            return route(
                "grp.org.procurement.org_agents.show.purchase-orders.index",
                [route().params["organisation"], agent.slug])
        
        default:
            return ''
    }
}

function stockDeliveriesRoute(agent: Agent) {
    switch (route().current()) {
        case "grp.org.procurement.org_agents.index":
            return route(
                "grp.org.procurement.org_agents.show.stock-deliveries.index",
                [route().params["organisation"], agent.slug])
        
        default:
            return ''
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Code -->
        <template #cell(code)="{ item: agent }">
            <Link :href="agentRoute(agent)" class="primaryLink">
                {{ agent["code"] }}
            </Link>
        </template>

        <!-- Column: Location -->
        <template #cell(location)="{ item: agent }">
            <AddressLocation :data="agent['location']" />
        </template>

        <!-- Column: PO -->
        <template #cell(number_purchase_orders)="{ item: agent }">
            <Link :href=purchaseOrdersRoute(agent) class="secondaryLink">
                {{ agent.number_purchase_orders }}
            </Link>
        </template>

        <!-- Column: Supplier -->
        <template #cell(number_org_supplier_products)="{ item: agent }">
            <Link :href=productsRoute(agent) class="secondaryLink">
                {{ agent.number_org_supplier_products }}
            </Link>
        </template>

        <!-- Column: SP -->
        <template #cell(number_org_suppliers)="{ item: agent }">
            <Link :href=suppliersRoute(agent) class="secondaryLink">
                {{ agent.number_org_suppliers }}
            </Link>
        </template>
        <!-- Column: SD -->
        <template #cell(number_stock_deliveries)="{ item: agent }">
            <Link :href=stockDeliveriesRoute(agent) class="secondaryLink">
                {{ agent.number_stock_deliveries }}
            </Link>
        </template>
        
    </Table>
</template>
