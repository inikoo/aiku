<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { MarketplaceAgent } from "@/types/marketplace-agent";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import ProcurementMarketplaceAdoption from "@/Components/Elements/Specialised/ProcurementMarketplaceAdoption.vue";

const props = defineProps<{
    data: object,
    tab?: string
}>();


function marketplacesAgentRoute(marketplaceAgent: MarketplaceAgent) {
    switch (route().current()) {
        case "grp.org.procurement.agents.index":
            return route(
                "grp.org.procurement.agents.show",
                [route().params["organisation"], marketplaceAgent.slug]);
        case "grp.org.procurement.agents.show.suppliers.index":
            return route(
                "grp.org.procurement.agents.show.suppliers.show",
                [route().params["organisation"], marketplaceAgent.slug]
            );
    }
}


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(adoption)="{ item: agent }">
            <ProcurementMarketplaceAdoption :value="agent['adoption']" />
        </template>
        <template #cell(code)="{ item: agent }">
            <Link :href="marketplacesAgentRoute(agent)">
                {{ agent["code"] }}
            </Link>
        </template>
        <template #cell(location)="{ item: agent }">
            <AddressLocation :data="agent['location']" />
        </template>
    </Table>
</template>


