<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 24 Mar 2024 21:16:55 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {StockFamily} from "@/types/stock-family";

const props = defineProps<{
    data: object,
    tab?: string
}>()

function stockFamilyRoute(stockFamily: StockFamily) {
    switch (route().current()) {

        case 'grp.goods.stock-families.index':
            return route(
                'grp.goods.stock-families.show',
                [stockFamily.slug, stockFamily.slug]);
    }

}



</script>



<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: stockFamily }">
            <Link :href="stockFamilyRoute(stockFamily)" class="specialUnderline">
                {{ stockFamily['code'] }}
            </Link>
        </template>
        <template #cell(name)="{ item: stockFamily }">
                {{ stockFamily['name'] }}
        </template>
        <template #cell(number_stocks)="{ item: stockFamily }">
            <Link :href="route('grp.goods.stock-families.show.stocks.index',stockFamily['slug'])">
                {{ stockFamily['number_stocks'] }}
            </Link>
        </template>
    </Table>
</template>
