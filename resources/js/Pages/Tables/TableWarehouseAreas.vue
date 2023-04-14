<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
const props = defineProps<{
    data: {
        table: object
        createInlineModel?: {
            buttonLabel: string
        }
    }
}>();
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { WarehouseArea } from "@/types/warehouse-area";
import Button from "@/Components/Elements/Buttons/Button.vue";


function warehouseAreaRoute(warehouseArea: WarehouseArea) {

    switch (route().current()) {
        case "inventory.warehouses.show":
        case "inventory.warehouses.show.warehouse-areas.index":
            return route(
                "inventory.warehouses.show.warehouse-areas.show",
                [warehouseArea.warehouse_slug, warehouseArea.slug]);
        case "inventor.warehouse-areas.index":
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
    <span v-if="data.createInlineModel" class="hidden sm:block text-end">
                <Button type="secondary" action="create" class="capitalize">
                 {{data.createInlineModel.buttonLabel}}
                </Button>
    </span>
    <Table :resource="data.table" :name="'wa'" class="mt-5">
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
