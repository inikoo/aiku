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
    data: object,
    tab?:string,
}>()


function locationRoute(location: Location) {
    switch (route().current()) {
        case 'grp.oms.warehouses.show':
        case 'grp.oms.warehouses.show.locations.index':
            return route(
                'grp.oms.warehouses.show.locations.show',
                [route().params['warehouse'], location.slug]);
        case 'grp.oms.warehouse-areas.show':
        case 'grp.oms.warehouse-areas.locations.index':
            return route(
                'grp.oms.warehouse-areas.show.locations.show',
                [route().params['warehouseArea'], location.slug]);

        case 'grp.oms.warehouses.show.warehouse-areas.show':
        case 'grp.oms.warehouses.show.warehouse-areas.show.locations.index':
            return route(
                'grp.oms.warehouses.show.warehouse-areas.show.locations.show',
                [route().params['warehouse'],route().params['warehouseArea'], location.slug]);
        default:
            return route(
                'grp.oms.locations.show',
                [location.slug]);
    }

}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: location }">
            <Link :href="locationRoute(location)">
                {{ location['code'] }}
            </Link>
        </template>
    </Table>
</template>


