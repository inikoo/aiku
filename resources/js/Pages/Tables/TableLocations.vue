<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Location} from "@/types/location";

const props = defineProps<{
    data: object
}>()



const itemRoute = route().current().replace(/index$/i, 'show')

function routeParameters(location: Location) {
    switch (route().current()) {
        case 'inventory.warehouses.show.locations.index':
            return [location.warehouse_slug, location.slug]
        case 'inventory.warehouse-areas.show.locations.index':
            return [location.warehouse_area_slug, location.slug]
        case 'inventory.warehouses.show.warehouse-areas.show.locations.index':
            return [location.warehouse_slug, location.warehouse_area_slug, location.slug]
        default:
            return [location.slug]
    }
}

</script>

<template>
    <Table :resource="data" :name="'loc'" class="mt-5">
        <template #cell(code)="{ item: location }">
            <Link :href="route(itemRoute,routeParameters(location))">
                {{ location.code }}
            </Link>
        </template>
    </Table>
</template>


