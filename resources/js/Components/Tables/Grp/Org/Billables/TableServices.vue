

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 18 Dec 2024 22:07:40 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import Icon from "@/Components/Icon.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faRobot } from '@fal'
import { useLocaleStore } from '@/Stores/locale'

library.add(faRobot)

const props = defineProps<{
    data: {}
    state: string
    tab?: string
}>()

const emits = defineEmits<{
    (e: 'renderTableKey'): void
}>()

function serviceRoute(service: {}) {
    switch (route().current()) {

        case "grp.org.shops.show.billables.services.index":
            return route(
                'grp.org.shops.show.billables.services.show',
                [route().params['organisation'], route().params['shop'], service.slug])
        default:
            return null
    }
}


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Code -->
        <template #cell(code)="{ item: service }">
            <component :is="serviceRoute(service) ? Link : 'div'" :href="serviceRoute(service) || '#'" :class="serviceRoute(service) ? 'primaryLink' : ''">
                {{ service['code'] }}
            </component>
        </template>



        <!-- Column: Icon -->
        <template #cell(state)="{ item: service }">
            <Icon :data="service['state_icon']" />
        </template>

        <!-- Column: Price -->
        <template #cell(price)="{ item: service }">
            {{ useLocaleStore().currencyFormat(service['currency_code'], service['price']) }} /{{
                service['unit_abbreviation'] }}
        </template>




    </Table>
</template>
