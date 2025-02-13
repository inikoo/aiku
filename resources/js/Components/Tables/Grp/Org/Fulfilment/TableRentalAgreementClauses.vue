<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 19 Jun 2024 15:34:14 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from '@/Components/Table/Table.vue'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faRobot } from '@fal'
import { Link } from "@inertiajs/vue3";
import Tag from '@/Components/Tag.vue';


library.add(faRobot)

defineProps<{
    data: object
    tab?: string,
}>()


function rentalAgreement(rental : any) {
    switch (rental) {
        case rental.asset_type === 'service':
            return route(
                "grp.org.fulfilments.show.catalogue.services.index",
                [
                    route().params["organisation"],
                    route().params["shop"],
                ]);
        case rental.asset_type === 'rental':
            return route(
                "grp.org.fulfilments.show.catalogue.rentals.index",
                [
                    route().params["organisation"],
                    route().params["shop"],
                ]);
        default:
           return null
    }
}


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
<!-- TODO: make logic for the show route,  check if the type is service , rental or product and use the route related to that type-->

        <template #cell(asset_code)="{ item: item }">
            <Link :href="rentalAgreement(item)" class="primaryLink">
                {{ item["asset_code"] }}
            </Link>
        </template>
        <template #cell(percentage_off)="{ item: item }">
           <Tag :label="item.percentage_off"/>
        </template>
    </Table>
</template>
