<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 18 Mar 2024 11:36:11 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import { Agent } from "@/types/agent";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";

const props = defineProps<{
    data: object,
    tab?:string
}>()


function marketplacesAgentRoute(agent: Agent) {
    switch (route().current()) {
        case 'grp.supply-chain.agents.index':
            return route(
                'grp.supply-chain.agents.show',
                [agent.slug]);
        case 'grp.supply-chain.agents.show.suppliers.index':
            return route(
                'grp.supply-chain.agents.show.suppliers.show',
                [agent.slug]
            )
    }
}




</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: agent }">
            <Link :href="marketplacesAgentRoute(agent)" class="primaryLink">
                {{ agent['code'] }}
            </Link>
        </template>
        <template #cell(location)="{ item: agent }">
            <AddressLocation :data="agent['location']"/>
        </template>
    </Table>
</template>


