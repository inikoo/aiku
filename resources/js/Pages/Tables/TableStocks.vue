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


function orderRoute(stock: Stock) {
    switch (route().current()) {
        case 'inventory.stocks.index':
            return route(
                'inventory.stocks.show',
                [stock.stocks.slug, stock.id]);
        default:
            return route(
                'orders.show',
                [stock.id]);
    }
}

function stockFamilyRoute(stock: Stock) {
    switch (route().current()) {
        case 'inventory.stocks.index':
            return route(
                'inventory.show',
                [stock.stocks.slug]);
        default:
            return route(
                'inventory.show',
                [stock.stocks.slug]);
    }
}




</script>

<template>
    <Table :resource="data" :name="'sm'" class="mt-5">
        <template #cell(code)="{ item: stock }">
            <Link :href="orderRoute(stock)">
                {{ stock['number'] }}
            </Link>
        </template>
        <template #cell(stockFamily)="{ item: stock }">
            <Link :href="stockFamilyRoute(stock)">
                {{ stock['shop'] }}
            </Link>
        </template>
    </Table>
</template>


