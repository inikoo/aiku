<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import Icon from '@/Components/Icon.vue'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTrashAlt } from '@far'
import { faSignOutAlt } from '@fal'
import StoredItemMovement from '@/Components/StoredItemMovement/StoredItemMovement.vue'
import { routeType } from '@/types/route'

library.add(
    faTrashAlt, faSignOutAlt
)
const props = defineProps<{
    data: object,
    tab?: string
    palletRoute: {
        index: routeType
    }
    locationRoute: {
        index: routeType
    }
    updateRoute: routeType
}>()

function palletRoutes(pallet: Pallet) {
    console.log("test123")
    switch (route().current()) {
        case 'grp.org.fulfilments.show.crm.customers.show.stored-items.index':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    pallet.pallet_slug
                ])
        case 'grp.org.warehouses.show.inventory.pallets.current.index':
            return route(
                'grp.org.warehouses.show.inventory.pallets.current.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    pallet['pallet_slug']
                ])

        case 'grp.org.warehouses.show.infrastructure.locations.show':
            return route(
                'grp.org.warehouses.show.infrastructure.locations.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    route().params['location'],
                    pallet['pallet_slug']
                ])
        case 'grp.org.fulfilments.show.crm.customers.show':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    pallet['pallet_slug']
                ])
        case 'grp.org.fulfilments.show.crm.customers.show.stored-items.show':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    pallet['pallet_slug']
                ])

        default:
            return []
    }
}

const generateLinkStoredItems = (storedItem: {}) => {
    switch (route().current()) {
        case 'grp.org.fulfilments.show.crm.customers.show.stored-items.index':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.stored-items.show',
                {
                    organisation: route().params['organisation'],
                    fulfilment: route().params['fulfilment'],
                    fulfilmentCustomer: route().params['fulfilmentCustomer'],
                    storedItem: storedItem.stored_item_slug,
                    tab: 'showcase'
                });
        case 'grp.org.fulfilments.show.crm.customers.show.stored-items.show':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    storedItem['slug']
                ])
        case 'retina.fulfilment.itemised_storage.stored_items.show':
            return route(
                'retina.fulfilment.storage.pallets.show',
                [
                    storedItem['slug']
                ])
        default:
            '#'
    }
}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(pallet_reference)="{ item: pallet }">
            <Link :href="palletRoutes(pallet)" class="primaryLink">
                {{ pallet['pallet_reference'] }}
            </Link>
        </template>

        <template #cell(stored_item_state)="{ item: pallet }">
            {{ pallet.stored_item_state_icon }}
            <Icon :data="pallet['stored_item_state_icon']" class="px-1" />
        </template>

        <template #cell(state)="{ item: pallet }">
            <Icon :data="pallet['state_icon']" class="px-1" />
        </template>

        <template #cell(reference)="{ item: pallet }">
            <Link v-if="pallet?.slug" :href="generateLinkStoredItems(pallet)" class="primaryLink">
                {{ pallet['reference'] }}
            </Link>
            <div v-else>
                {{ pallet['reference'] }}
            </div>
        </template>

        <template #cell(actions)="{ item: pallet }">
            <div>
                <StoredItemMovement :palletRoute="palletRoute" :locationRoute="locationRoute" :pallet="pallet"
                    :updateRoute="updateRoute" />
            </div>
        </template>
    </Table>
</template>
