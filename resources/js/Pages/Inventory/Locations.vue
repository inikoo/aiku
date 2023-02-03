<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 17 Sept 2022 00:32:56 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

const props = defineProps<{
    records: object
    title: string
    pageHead: object
}>()


const itemRoute = route().current().replace(/index$/i, 'show')

function routeParameters(location) {
    switch (route().current()) {
        case 'inventory.warehouses.show.locations.index':
            return [location.warehouse_id,location.slug]
        case 'inventory.warehouse_areas.show.locations.index':
            return [location.warehouse_area_slug,location.slug]
        case 'inventory.warehouses.show.warehouse_areas.show.locations.index':
            return [location.warehouse_slug,location.warehouse_area_slug,location.slug]
        default:
            return [location.slug]
    }
}

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="records" class="mt-5">
        <template #cell(code)="{ item: location }">
            <Link :href="route(itemRoute,routeParameters(location))">
                {{ location.code }}
            </Link>
        </template>

    </Table>
</template>


