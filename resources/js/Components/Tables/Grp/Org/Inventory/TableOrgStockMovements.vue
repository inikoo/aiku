<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 24 Mar 2024 21:09:00 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { Stock } from "@/types/stock";

defineProps<{
  data: object
  tab?: string
}>();


function stockRoute(stock: Stock) {


  console.log(route().current());
  switch (route().current()) {
    case "grp.org.warehouses.show.inventory.org_stock_families.show":
      return route(
        "grp.org.warehouses.show.inventory.org_stock_families.show.org_stocks.show",
        [
          route().params["organisation"],
          route().params["warehouse"],
          route().params["orgStockFamily"],
          stock.slug
        ]);
    case "grp.org.warehouses.show.inventory.org_stocks.current_org_stocks.index":
      return route(
        "grp.org.warehouses.show.inventory.org_stocks.current_org_stocks.show",
        [
          route().params["organisation"],
          route().params["warehouse"],
          stock.slug
        ]);
    case "grp.overview.inventory.org-stocks.index":
      return route(
        "grp.org.warehouses.show.inventory.org_stocks.current_org_stocks.show",
        [
          stock.organisation_slug,
          stock.warehouse_slug,
          stock.slug
        ]);
    case "grp.org.warehouses.show.inventory.org_stocks.index":
      return route(
        "grp.org.warehouses.show.inventory.org_stocks.show",
        [
          route().params["organisation"],
          route().params["warehouse"],
          stock.slug
        ]);
    default:
        return null
  }
}



</script>

<template>

  <Table :resource="data" :name="tab" class="mt-5">
  </Table>
</template>


