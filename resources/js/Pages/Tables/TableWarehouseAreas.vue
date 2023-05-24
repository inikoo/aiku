<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

const props = defineProps<{
    data: object
}>()

import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { WarehouseArea } from "@/types/warehouse-area";

function warehouseAreaRoute(warehouseArea: WarehouseArea) {

    switch (route().current()) {
        case "inventory.warehouses.show":
        case "inventory.warehouses.show.warehouse-areas.index":
            return route(
                "inventory.warehouses.show.warehouse-areas.show",
                [
                    warehouseArea.warehouse_slug,
                    warehouseArea.slug
                ]);



        case "inventory.warehouse-areas.index":
        default:
            return route(
                "inventory.warehouse-areas.show",
                [warehouseArea.slug]);
    }

}

function locationsRoute(warehouseArea: WarehouseArea) {
    switch (route().current()) {

        case "inventory.warehouses.show":
        case "inventory.warehouses.show.warehouse-areas.index":
            return route(
                "inventory.warehouses.show.warehouse-areas.show.locations.index",
                [warehouseArea.warehouse_slug, warehouseArea.slug]);
        case "inventor.warehouse-areas.index":
        default:
            return route(
                "inventory.warehouse-areas.show.locations.index",
                [warehouseArea.slug]);
    }

}


</script>


<template>
    <Table :resource="data" :name="'wa'" class="mt-5">
        <template #cell(code)="{ item: warehouseArea }">
            <Link :href="warehouseAreaRoute(warehouseArea)">
                {{ warehouseArea["code"] }}
            </Link>
        </template>

        <template #cell(number_locations)="{ item: warehouseArea }">
            <Link :href="locationsRoute(warehouseArea)">
                {{ warehouseArea["number_locations"] }}
            </Link>
        </template>
    </Table>
</template>
