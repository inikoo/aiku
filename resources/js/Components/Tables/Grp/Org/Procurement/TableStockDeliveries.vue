<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';

const props = defineProps<{
    data: object,
    tab?: string
}>()


function stockDeliveryRoute(stockDelivery: { } ) {
    switch (route().current()) {
        case 'grp.org.procurement.stock-deliveries.index':
            return route(
                'grp.org.procurement.stock-deliveries.show',
                [stockDelivery.slug]);
        default:
            return route(
                'grp.org.procurement.stock-deliveries.show',
                [stockDelivery.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(number)="{ item: stockDelivery }">
            <Link :href="stockDeliveryRoute(stockDelivery)">
                {{ stockDelivery['number'] }}
            </Link>
        </template>
    </Table>
</template>


