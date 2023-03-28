<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {StockFamily} from "@/types/stock-family";
import {useLocaleStore} from '@/Stores/locale.js';

const props = defineProps<{
    data: object
}>()

const locale = useLocaleStore();

function stockFamilyRoute(stockFamily: StockFamily) {
    switch (route().current()) {
        case 'inventory.stock-families.index':
            return route(
                'inventory.stock-families.show',
                [stockFamily.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'sf'" class="mt-5">
        <template #cell(code)="{ item: stockFamily }">
            <Link :href="stockFamilyRoute(stockFamily)">
                {{ stockFamily['code'] }}
            </Link>
        </template>
        <template #cell(number_stocks)="{ item: stockFamily }">
            {{ locale.number(stockFamily['number_stocks']) }}
        </template>

    </Table>
</template>


