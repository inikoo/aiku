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

function masterShopRoute(masterShop: {}) {
    switch (route().current()) {
        case 'grp.goods.catalogue.shops.index':
            return route(
                'grp.goods.catalogue.shops.show',
                [masterShop.slug]);

    }
}




</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: masterShop }">
            <Link :href="masterShopRoute(masterShop)" class="primaryLink">
                {{ masterShop['code'] }}
            </Link>
        </template>
        <template #cell(name)="{ item: masterShop }">
            {{ masterShop['name'] }}
        </template>
    </Table>
</template>


