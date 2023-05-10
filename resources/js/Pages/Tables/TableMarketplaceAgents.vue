<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import { MarketplaceAgent } from "@/types/marketplace-agent";

const props = defineProps<{
    data: object
}>()


function marketplacesAgentRoute(marketplaceAgent: MarketplaceAgent) {
    switch (route().current()) {
        case 'procurement.marketplace-agents.index':
            return route(
                'procurement.marketplace-agents.show',
                [marketplaceAgent.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'ag'" class="mt-5">
        <template #cell(code)="{ item: marketplaceAgent }">
            <Link :href="marketplacesAgentRoute(marketplaceAgent)">
                {{ marketplaceAgent['code'] }}
            </Link>
        </template>
    </Table>
</template>


