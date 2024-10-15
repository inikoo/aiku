<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';

const props = defineProps<{
    data: {}
    tab?: string
}>()

// function tradeUnitRoute(tradeUnit: {}) {
//     switch (route().current()) {
//         case 'grp.goods.trade-units.index':
//             return route(
//                 'grp.goods.trade-units.show',
//                 [tradeUnit.slug]);

//     }
// }




</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: masterProducts }">
            {{ masterShop['code'] }}
        </template>
        <template #cell(name)="{ item: masterProducts }">
            {{ masterShop['name'] }}
        </template>
    </Table>
</template>


