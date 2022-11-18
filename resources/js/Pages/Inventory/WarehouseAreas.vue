<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 20:33:56 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="records" class="mt-5">
        <template #cell(code)="{ item: warehouseArea }">
            <Link :href="route(warehouseAreaRoute,warehouseAreaRouteParameters(warehouseArea))">
                {{ warehouseArea.code }}
            </Link>
        </template>
        <template #cell(number_locations)="{ item: warehouseArea }">
            <Link :href="route(locationRoute,warehouseAreaRouteParameters(warehouseArea))">
                {{ warehouseArea['number_locations'] }}
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

const warehouseAreaRoute = route().current().replace(/index$/i, 'show')

const locationRoute = warehouseAreaRoute + '.locations.index'



function warehouseAreaRouteParameters(warehouseArea) {
    switch (route().current()) {
        case 'inventory.warehouse_areas.index':
            return [warehouseArea.slug]
        case 'inventory.warehouses.show.warehouse_areas.index':
            return [warehouseArea.warehouse_slug, warehouseArea.slug]
        default:
            return []
    }
}
</script>
