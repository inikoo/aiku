<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Stock} from "@/types/stock";

const props = defineProps<{
    data: object
    tab?: string
}>()


function stockRoute(stock: Stock) {
    switch (route().current()) {
        case 'grp.goods.stock-families.show.stocks.index':
            return route(
                'grp.goods.stock-families.show.stocks.show',
                [route().params['stockFamily'],stock.slug]);
        default:
            return route(
                'grp.goods.stocks.show',
                [
                    stock.slug
                ]);
    }
}

function stockFamilyRoute(stock: Stock) {
    switch (route().current()) {
        case 'grp.goods.stocks.index':
            return route(
                'grp.goods.stock-families.show',
                [stock.family_slug]);

    }
}




</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: stock }">
            <Link :href="stockRoute(stock)" class="primaryLink">
                {{ stock['code'] }}
            </Link>
        </template>
        <template #cell(family_code)="{ item: stock }">
            <Link v-if="stock.family_slug"  :href="stockFamilyRoute(stock)" class="secondaryLink">
                {{ stock['family_code'] }}
            </Link>
        </template>
        <template #cell(description)="{ item: stock }">
            {{ stock['description'] }}
        </template>
        <template #cell(unit_value)="{ item: stock }">
            {{ stock['unit_value'] }}
        </template>
    </Table>
</template>


