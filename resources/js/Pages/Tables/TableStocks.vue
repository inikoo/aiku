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
}>()


function stockRoute(stock: Stock) {
    switch (route().current()) {
        case 'inventory.stocks.index':
            return route(
                'inventory.stocks.show',
                [stock.slug]);
        default:
            return route(
                'inventory.stocks.show',
                [stock.slug]);
    }
}

function stockFamilyRoute(stock: Stock) {
    switch (route().current()) {
        case 'inventory.stocks.index':
            return route(
                'inventory.stock-families.show',
                [stock.family_slug]);
        default:
            return route(
                'inventory.stock-families.show',
                [stock.family_slug]);
    }
}




</script>

<template>
    <Table :resource="data" :name="'sm'" class="mt-5">
        <template #cell(code)="{ item: stock }">
            <Link :href="stockRoute(stock)">
                {{ stock['code'] }}
            </Link>
        </template>
        <template #cell(family_code)="{ item: stock }">
            <Link v-if="stock.family_slug"  :href="stockFamilyRoute(stock)">
                {{ stock['family_code'] }}
            </Link>
        </template>
    </Table>
</template>


