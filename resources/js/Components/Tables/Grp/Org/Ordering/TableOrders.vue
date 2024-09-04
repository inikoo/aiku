<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { Order } from "@/types/order"
import type { Links, Meta } from "@/types/Table"
import { useFormatTime } from '@/Composables/useFormatTime'

defineProps<{
    data: {
        data: {}[]
        links: Links
        meta: Meta
    },
    tab?: string
}>()


function orderRoute(order: Order) {
    console.log(route().current())
    switch (route().current()) {
        case "grp.org.shops.show.crm.show.orders.index":
            return route(
                "grp.org.shops.show.crm.show.orders.show",
                [route().params["organisation"], , route().params["shop"], route().params["customer"], order.slug])
        case "grp.org.shops.show.ordering.orders.index":
            return route(
                "grp.org.shops.show.ordering.orders.show",
                [route().params["organisation"], route().params["shop"], order.slug])
        case "grp.org.shops.show.crm.customers.show.orders.index":
            return route(
                "grp.org.shops.show.crm.customers.show.orders.show",
                [route().params["organisation"], route().params["shop"], route().params["customer"], order.slug])
        case "grp.org.shops.show.crm.customers.show.customer-clients.orders.index":
            return route(
                "grp.org.shops.show.crm.customers.show.customer-clients.orders.show",
                [route().params["organisation"], route().params["shop"], route().params["customer"], route().params["customerClient"], order.slug])
        default:
            return null
    }
}

function shopRoute(order: Order) {
    switch (route().current()) {
        default:
            return route(
                "shops.show",
                [order.shop_slug])
    }
}


function customerRoute(order: Order) {
    switch (route().current()) {
        case "grp.org.shops.show.ordering.orders.index":
            return route(
                "grp.org.shops.show.crm.customers.show",
                [route().params["organisation"], route().params["shop"], order.customer_slug])
        default:
            return route(
                "grp.org.shops.show.crm.customers.show",
                [route().params["organisation"], route().params["shop"], order.customer_slug])
    }
}


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Reference -->
        <template #cell(reference)="{ item: order }">
            <Link :href="orderRoute(order)" class="primaryLink">
                {{ order["reference"] }}
            </Link>
        </template>

        <!-- Column: Customer -->
        <template #cell(customer_name)="{ item: order }">
            <Link :href="customerRoute(order)" class="secondaryLink">
                {{ order["customer_name"] }}
            </Link>
        </template>

        <!-- Column: Shop -->
        <template #cell(shop)="{ item: order }">
            <Link :href="shopRoute(order)">
                {{ order["shop"] }}
            </Link>
        </template>

        <!-- Column: Date -->
        <template #cell(date)="{ item: order }">
            {{ useFormatTime(order.date) }}
        </template>
    </Table>
</template>
