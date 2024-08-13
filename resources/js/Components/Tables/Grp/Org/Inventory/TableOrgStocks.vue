<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 24 Mar 2024 21:09:00 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Stock} from "@/types/stock";

defineProps<{
    data: object
    tab?: string
}>()


function stockRoute(stock: Stock) {


  console.log(route().current())
    switch (route().current()) {
      case 'grp.org.inventory.org_stocks.current_org_stocks.index':
        return route(
          'grp.org.inventory.org_stocks.current_org_stocks.show',
          [
            route().params['organisation'],
            stock.slug
          ]);
        default:
            return route(
                'grp.org.inventory.org_stocks.show',
                [
                    route().params['organisation'],
                    stock.slug
                ]);
    }
}

function stockFamilyRoute(stock: Stock) {
    switch (route().current()) {



        default:
            return route(
                'grp.org.inventory.org_stock_families.show',
                [
                    route().params['organisation'],
                    stock.family_slug
                ]);
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
            <!--suppress TypeScriptUnresolvedReference -->
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


