<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { WarehouseArea } from "@/types/warehouse-area";

const props = defineProps<{
    data: object,
    tab?: string
}>();

function warehouseAreaRoute(warehouseArea: WarehouseArea) {
    console.log(route().current());
    switch (route().current()) {
        case "grp.overview.inventory.warehouses-areas.index":
            return route(
                "grp.org.warehouses.show.infrastructure.warehouse_areas.show",
                [
                    warehouseArea.organisation_slug,
                    warehouseArea.warehouse_slug,
                    warehouseArea.slug
                ]
            );
        case "grp.org.warehouses.show.infrastructure.warehouse_areas.index":
        default:
            return route(
                "grp.org.warehouses.show.infrastructure.warehouse_areas.show",
                [
                    route().params["organisation"],
                    route().params["warehouse"],
                    warehouseArea.slug
                ]
            );

    }

}

function locationsRoute(warehouseArea: WarehouseArea) {
    switch (route().current()) {
        case "grp.overview.inventory.warehouses-areas.index":
            return route(
                "grp.org.warehouses.show.infrastructure.warehouse_areas.show.locations.index",
                [
                    warehouseArea.organisation_slug,
                    warehouseArea.warehouse_slug,
                    warehouseArea.slug
                ]
            );
        case "grp.org.warehouses.show.infrastructure.warehouse_areas.index":
        default:
            return route(
                "grp.org.warehouses.show.infrastructure.warehouse_areas.show.locations.index",
                [
                    route().params["organisation"],
                    route().params["warehouse"],
                    warehouseArea.slug
                ]);

    }

}


</script>


<template>

    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(name)="{ item: warehouseArea }">
            <Link :href="warehouseAreaRoute(warehouseArea)" class="primaryLink">
                {{ warehouseArea["name"] }}
            </Link>
        </template>

        <template #cell(number_locations)="{ item: warehouseArea }">
            <Link :href="locationsRoute(warehouseArea)" class="primaryLink">
                {{ warehouseArea["number_locations"] }}
            </Link>
        </template>
    </Table>
</template>
