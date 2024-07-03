<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Apr 2024 15:15:47 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import Icon from "@/Components/Icon.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faRobot } from '@fal'
import { useLocaleStore } from '@/Stores/locale'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

library.add(faRobot)

defineProps<{
    data: object
    tab?: string,
}>()


function rentalRoute(rental: {}) {
    console.log(route().current())
    switch (route().current()) {

        case "grp.org.fulfilments.show.billables.rentals.index":
            return route(
                'grp.org.fulfilments.show.billables.rentals.show',
                [route().params['organisation'], route().params['fulfilment'], rental.slug])
        default:
            return null
    }
}


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Code -->
        <template #cell(code)="{ item: rental }">
            <Link :href="rentalRoute(rental)" class="primaryLink">
                {{ rental['code'] }}
            </Link>
        </template>

        <!-- Column: Shop Code -->
        <template #cell(shop_code)="{ item: rental }">
            <Link v-if="rental['shop_slug']" :href="rentalRoute(rental)" class="secondaryLink">
                {{ rental['shop_slug'] }}
            </Link>
        </template>

        <!-- Column: Icon -->
        <template #cell(state)="{ item: rental }">
            <Icon :data="rental['state_icon']" />
        </template>

        <!-- Column: Price -->
        <template #cell(price)="{ item: rental }">
            {{ useLocaleStore().currencyFormat(rental['currency_code'], rental['price']) }} /{{
                rental['unit_abbreviation'] }}
        </template>

        <!-- Column: Workflow -->
        <template #cell(workflow)="{ item: rental }">
            <template v-if="rental['auto_assign_asset']">
                <FontAwesomeIcon icon='fal fa-robot' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                {{ rental['auto_assign_asset'] }}: {{ rental['auto_assign_asset_type'] }}
            </template>
        </template>
    </Table>
</template>
