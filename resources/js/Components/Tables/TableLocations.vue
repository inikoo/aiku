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
        case 'grp.org.warehouses.show.infrastructure.dashboard':
        case 'grp.org.warehouses.show.infrastructure.locations.index':
            return route(
                'grp.org.warehouses.show.infrastructure.locations.show',
                [route().params['organisation'], route().params['warehouse'], location.slug]);
        case 'grp.org.warehouse-areas.show':
        case 'grp.org.warehouse-areas.locations.index':
            return route(
                'grp.org.warehouse-areas.show.locations.show',
                [route().params['organisation'], route().params['warehouseArea'], location.slug]);

        case 'grp.org.warehouses.show.infrastructure.warehouse-areas.show':
        case 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.index':
            return route(
                'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.show',
                [route().params['organisation'], route().params['warehouse'],route().params['warehouseArea'], location.slug]);
        default:
            return route(
                'grp.org.locations.show',
                [route().params['organisation'], location.slug]);
    }

}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: location }">
            <Link :href="locationRoute(location)" class="specialUnderline">
                {{ location['code'] }}
            </Link>
        </template>

        <template #cell(scope)="{ item: location }">
           Stocks: {{location['allow_stocks']}} {{location['has_stock_slots']}}
            Dropshipping: {{location['allow_dropshipping']}} : {{location['has_dropshipping_slots']}}
            Fulfilment: {{location['allow_fulfilment']}} {{location['has_fulfilment']}}

        </template>
    </Table>
</template>


