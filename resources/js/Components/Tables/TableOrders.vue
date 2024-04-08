<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import {Order} from "@/types/order";
import type {Links, Meta} from "@/types/Table";

const props = defineProps<{
  data: {
    data: {}[]
    links: Links
    meta: Meta
  },
  tab?: string
}>();


function orderRoute(order: Order) {
  console.log(route().current())
  switch (route().current()) {
    case "shops.show.customers.show":
      return route(
          "shops.show.customers.show.orders.show",
          [route().params["shop"], route().params["customer"], order.slug]);
    case "customers.show":
      return route(
          "customers.show.orders.show",
          [route().params["customer"], order.slug]);
    case "shops.show.orders.index":
      return route(
          "shops.show.orders.show",
          [order.shop_slug, order.slug]);
    default:
      return null;
  }
}

function shopRoute(order: Order) {
  switch (route().current()) {
    default:
      return route(
          "shops.show",
          [order.shop_slug]);
  }
}


</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">
    <template #cell(number)="{ item: order }">
      <Link :href="orderRoute(order)">
        {{ order["number"] }}
      </Link>
    </template>
    <template #cell(shop)="{ item: order }">
      <Link :href="shopRoute(order)">
        {{ order["shop"] }}
      </Link>
    </template>
  </Table>
</template>


