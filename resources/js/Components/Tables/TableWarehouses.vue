<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { Warehouse } from "@/types/warehouse"
import { useLocaleStore } from '@/Stores/locale'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

import '@/Composables/Icon/WarehouseStateIcon'

const props = defineProps<{
    data: {}
    tab?: string
}>()

const locale = useLocaleStore()

function warehouseRoute(warehouse: Warehouse) {
    switch (route().current()) {
        case 'grp.org.warehouses.index':
            return route(
                'grp.org.warehouses.show.infrastructure.dashboard',
                [route().params['organisation'], warehouse.slug])
    }
}

function warehouseAreasRoute(warehouse: Warehouse) {
    switch (route().current()) {
        case 'grp.org.warehouses.index':
            return route(
                'grp.org.warehouses.show.infrastructure.warehouse-areas.index',
                [route().params['organisation'], warehouse.slug])
    }
}

function locationsRoute(warehouse: Warehouse) {
    switch (route().current()) {
        case 'grp.org.warehouses.index':
            return route(
                'grp.org.warehouses.show.infrastructure.locations.index',
                [route().params['organisation'], warehouse.slug])
    }
}

</script>


<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Code -->
        <template #cell(code)="{ item: warehouse }">
            <Link :href="warehouseRoute(warehouse)" class="specialUnderline">
                {{ warehouse['code'] }}
            </Link>
        </template>

        <!-- Column: Warehouse Areas -->
        <template #cell(number_warehouse_areas)="{ item: warehouse }">
            <Link :href="warehouseAreasRoute(warehouse)" class="specialUnderline">
                {{ locale.number(warehouse['number_warehouse_areas'] || 0) }}
            </Link>
        </template>
        
        <!-- Column: State -->
        <template #cell(state)="{ item: warehouse }">
            <div v-tooltip="warehouse.state_icon.tooltip" class="px-1 py-0.5">
                <FontAwesomeIcon :icon='warehouse.state_icon.icon' :class='warehouse.state_icon.class' fixed-width aria-hidden='true' />
            </div>
        </template>

        <!-- Column: Locations -->
        <template #cell(number_locations)="{ item: warehouse }">
            <Link :href="locationsRoute(warehouse)" class="specialUnderline">
                {{ locale.number(warehouse['number_locations'] || 0) }}
            </Link>
        </template>
    </Table>
</template>
