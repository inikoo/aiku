<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
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

        case 'grp.oms.stock-families.index':
            return route(
                'grp.oms.stock-families.show',
                [stockFamily.slug, stockFamily.slug]);
    }

}



</script>



<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: stockFamily }">
            <Link :href="stockFamilyRoute(stockFamily)">
                {{ stockFamily['code'] }}
            </Link>
        </template>
        <template #cell(name)="{ item: stockFamily }">
                {{ stockFamily['name'] }}
        </template>
        <template #cell(number_stocks)="{ item: stockFamily }">
            <Link :href="route('grp.inventory.stock-families.show.stocks.index',stockFamily['slug'])">
                {{ stockFamily['number_stocks'] }}
            </Link>
        </template>
    </Table>
</template>
