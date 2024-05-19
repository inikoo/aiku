<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import TagPallete from "@/Components/TagPallete.vue"
import Icon from "@/Components/Icon.vue"
import { inject } from 'vue'

const layout = inject('layout')

const props = defineProps<{
    data: {}
    tab?: string
}>()

function storedItemRoute(storedItem) {
    switch (route().current()) {
        case 'grp.org.fulfilments.show.crm.customers.show':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.stored-item-returns.show',
                [route().params['organisation'], route().params['fulfilment'], route().params['fulfilmentCustomer'], storedItem.slug])
        case 'retina.storage.stored-item-returns.index':
            return route(
                'retina.storage.stored-item-returns.show',
                [storedItem.slug])
        default:
            return route(
                'grp.fulfilment.stored-items.show',
                [storedItem.slug])
    }
}

</script>

<template>
    <Table :resource="data" :name="'stored_item_returns'" class="mt-5">
        <template #cell(reference)="{ item: value }">
            <Link :href="storedItemRoute(value)" class="primaryLink">
            {{ value.reference }}
            </Link>
        </template>

        <!-- Column: State -->
        <template #cell(state)="{ item: value }">
            <TagPallete v-if="layout.app.name === 'retina'" :stateIcon="value.state_icon" />
            <Icon v-else :data="value['state_icon']" class="px-1" />
        </template>
    </Table>
</template>
