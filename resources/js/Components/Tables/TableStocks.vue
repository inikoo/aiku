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
        case 'grp.oms.stock-families.show.stocks.index':
            return route(
                'grp.oms.stock-families.show.stocks.show',
                [stock.family_slug,stock.slug]);
        default:
            return route(
                'grp.org.inventory.stocks.show',
                [
                    route().params['organisation'],
                    stock.slug
                ]);
    }
}

function stockFamilyRoute(stock: Stock) {
    switch (route().current()) {
        case 'grp.oms.stocks.index':
            return route(
                'grp.oms.stock-families.show',
                [stock.family_slug]);
        default:
            return route(
                'grp.org.inventory.stock-families.show',
                [
                    route().params['organisation'],
                    stock.family_slug
                ]);
    }
}




</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: stock }">
            <Link :href="stockRoute(stock)">
                {{ stock['slug'] }}
            </Link>
        </template>
        <template #cell(family_code)="{ item: stock }">
            <!--suppress TypeScriptUnresolvedReference -->
            <Link v-if="stock.family_slug"  :href="stockFamilyRoute(stock)">
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


