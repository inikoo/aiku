<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Warehouse} from "@/types/warehouse";

const props = defineProps<{
    data: object,
    tab?: string
}>()

function warehouseRoute(warehouse: Warehouse) {
    switch (route().current()) {

        case 'grp.oms.warehouses.index':
            return route(
                'grp.oms.warehouses.show',
                [warehouse.slug, warehouse.slug]);
    }

}



</script>


<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: warehouse }">
            <Link :href="warehouseRoute(warehouse)">
                {{ warehouse['code'] }}
            </Link>
        </template>
        <template #cell(number_warehouse_areas)="{ item: warehouse }">
            <Link :href="route('grp.inventory.warehouses.show.warehouse-areas.index',warehouse['slug'])">
                {{ warehouse['number_warehouse_areas'] }}
            </Link>
        </template>
    </Table>
</template>
