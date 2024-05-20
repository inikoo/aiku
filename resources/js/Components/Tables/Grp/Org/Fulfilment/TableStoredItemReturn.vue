<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:46:51 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import TagPallet from "@/Components/TagPallet.vue"
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
            <TagPallet v-if="layout.app.name === 'retina'" :stateIcon="value.state_icon" />
            <Icon v-else :data="value['state_icon']" class="px-1" />
        </template>
    </Table>
</template>
