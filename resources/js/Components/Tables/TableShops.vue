<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Shop} from "@/types/shop";
const props = defineProps<{
    data: object,
    tab?:string,
}>()


function shopRoute(shop: Shop) {
    switch (route().current()) {
        case 'grp.org.shops.index':
            return route(
                'grp.org.shops.show',
                [route().params['organisation'], shop.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab"  class="mt-5">
        <template #cell(code)="{ item: shop }">
            <Link :href="shopRoute(shop)">
                {{ shop['code'] }}
            </Link>
        </template>
    </Table>


</template>


