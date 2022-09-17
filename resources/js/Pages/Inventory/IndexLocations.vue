<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 17 Sept 2022 00:32:56 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="records" class="mt-5">
        <template #cell(code)="{ item: location }">
            <Link :href="route(locationRoute,locationRouteParameters(location))">
                {{ location.code }}
            </Link>
        </template>

    </Table>
</template>

<script setup lang="ts">
import {Head, Link} from '@inertiajs/inertia-vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';


const props = defineProps<{
    records: object
    title: string
    pageHead: object
}>()


const locationRoute = route().current().replace(/index$/i, 'show')

function locationRouteParameters(location) {
    switch (route().current()) {
        case 'inventory.warehouses.show.locations.index':
            return [location.warehouse_id,location.id]
        case 'inventory.warehouse_areas.show.locations.index':
            return [location.warehouse_area_id,location.id]
        case 'inventory.warehouses.show.warehouse_areas.show.locations.index':
            return [location.warehouse_id,location.warehouse_area_id,location.id]
        default:
            return [location.id]
    }
}

</script>
