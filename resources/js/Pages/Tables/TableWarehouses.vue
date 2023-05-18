<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
const props = defineProps<{
    data: object
}>()
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Warehouse} from "@/types/warehouse";


function warehouseRoute(warehouse: Warehouse) {
    switch (route().current()) {

        case 'inventory.warehouses.index':
            return route(
                'inventory.warehouses.show',
                [warehouse.slug, warehouse.slug]);
    }

}



</script>


<template>
    <Table :resource="data" :name="'w'" class="mt-5">
        <template #cell(code)="{ item: warehouse }">
            <Link :href="warehouseRoute(warehouse)">
                {{ warehouse['code'] }}
            </Link>
        </template>
        <template #cell(number_warehouse_areas)="{ item: warehouse }">
            <Link :href="route('inventory.warehouses.show.warehouse-areas.index',warehouse.slug)">
                {{ warehouse['number_warehouse_areas'] }}
            </Link>
        </template>
    </Table>
</template>
