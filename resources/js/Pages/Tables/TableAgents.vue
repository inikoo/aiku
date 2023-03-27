<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Agent} from "@/types/agent";

const props = defineProps<{
    data: object
}>()


function agentRoute(agent: Agent) {
    switch (route().current()) {
        case 'procurement.agents.index':
            return route(
                'procurement.agents.show',
                [agent.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'ap'" class="mt-5">
        <template #cell(code)="{ item: agent }">
            <Link :href="agentRoute(agent)">
                {{ agent.slug }}
            </Link>
        </template>
    </Table>
</template>


