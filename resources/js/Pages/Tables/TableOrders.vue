<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Order} from "@/types/order";

const props = defineProps<{
    data: object
}>()


function orderRoute(order: Order) {
    switch (route().current()) {
        case 'shops.show.orders.index':
            return route(
                'shops.show.orders.show',
                [order.shop_slug, order.id]);
        default:
            return route(
                'orders.show',
                [order.id]);
    }
}

function shopRoute(order: Order) {
    switch (route().current()) {
        case 'shops.show.orders.index':
            return route(
                'shops.show',
                [order.shop_slug]);
        default:
            return route(
                'shops.show',
                [order.shop_slug]);
    }
}


</script>

<template>
    <Table :resource="data" :name="'o'" class="mt-5">
        <template #cell(number)="{ item: order }">
            <Link :href="orderRoute(order)">
                {{ order['number'] }}
            </Link>
        </template>
        <template #cell(shop)="{ item: order }">
            <Link :href="shopRoute(order)">
                {{ order['shop'] }}
            </Link>
        </template>
    </Table>
</template>


