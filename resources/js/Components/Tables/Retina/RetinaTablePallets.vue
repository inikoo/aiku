<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:45:58 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from '@/Components/Table/Table.vue'
import { library } from "@fortawesome/fontawesome-svg-core"

import { faTimes, faStickyNote } from '@fal'
import TagPallet from '@/Components/TagPallet.vue'
import Icon from '@/Components/Icon.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Tag from '@/Components/Tag.vue'

library.add(faTimes, faStickyNote)

const props = defineProps<{
    data: {}
    tab?: string
}>()


// function palletRoute(pallet: Pallet) {
//     switch (route().current()) {
//         case 'grp.org.fulfilments.show.operations.pallets.current.index':
//             return route(
//                 'grp.org.fulfilments.show.operations.pallets.current.show',
//                 [
//                     route().params['organisation'],
//                     route().params['fulfilment'],
//                     pallet['slug']
//                 ])
//         case 'grp.org.warehouses.show.inventory.pallets.current.index':
//             return route(
//                 'grp.org.warehouses.show.inventory.pallets.current.show',
//                 [
//                     route().params['organisation'],
//                     route().params['warehouse'],
//                     pallet['slug']
//                 ])

//         case 'grp.org.warehouses.show.infrastructure.locations.show':
//             return route(
//                 'grp.org.warehouses.show.infrastructure.locations.show.pallets.show',
//                 [
//                     route().params['organisation'],
//                     route().params['warehouse'],
//                     route().params['location'],
//                     pallet['slug']
//                 ])
//         case 'grp.org.fulfilments.show.crm.customers.show':
//             return route(
//                 'grp.org.fulfilments.show.crm.customers.show.pallets.show',
//                 [
//                     route().params['organisation'],
//                     route().params['fulfilment'],
//                     route().params['fulfilmentCustomer'],
//                     pallet['slug']
//                 ])

//         default:
//             return []
//     }
// }

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- <template #cell(reference)="{ item: pallet }">
            <Link :href="'palletRoute(pallet)'" class="primaryLink">
                {{ pallet['reference'] }}
            </Link>
        </template> -->

        <template #cell(state)="{ item: pallet }">
            <!-- <Icon v-if="pallet['state_icon']" :data="pallet['state_icon']" class="px-1" /> -->
            <TagPallet :stateIcon="pallet.state_icon" />
        </template>

        
        <template #cell(type_icon)="{ item: pallet }">
            <div class="space-y-1">
                <TagPallet :stateIcon="pallet.state_icon" v-tooltip="'Current state of this pallet'" />
                <TagPallet :stateIcon="pallet.status_icon" v-tooltip="'Current status of this pallet'" />
            </div>
        </template>

        <!-- Column: Pallet Reference -->
        <template #cell(reference)="{ item: pallet }">
            {{ pallet.reference }}
        </template>

        <!-- Column: Rental name -->
        <template #cell(rental)="{ item: pallet }">
            {{ pallet.rental_name }}
        </template>

        <!-- Column: Pallet Reference -->
        <template #cell(stored_items)="{ item: pallet }">
            <div v-if="pallet.stored_items.length" class="flex flex-wrap gap-x-1 gap-y-1.5">
                <Tag v-for="stored_item of pallet.stored_items" :theme="stored_item.id"
                    :label="`${stored_item.reference} (${stored_item.quantity})`" :closeButton="false" :stringToColor="true">
                    <template #label>
                        <div class="whitespace-nowrap text-xs">
                            {{ stored_item.reference }} (<span class="font-light">{{ stored_item.quantity }}</span>)
                        </div>
                    </template>
                </Tag>
            </div>
            <div v-else class="text-gray-400 text-xs italic">
                No items
            </div>
        </template>

        <template #cell(notes)="{ item }">
            <FontAwesomeIcon icon="fal fa-sticky-note" class="text-gray-400" fixed-width aria-hidden="true" />
            {{ item.notes }}
        </template>

        <!-- Column: Customer Reference -->
        <!-- <template #cell(customer_reference)="{ item: item }">
            <div>
                {{ item.customer_reference }}
                <span v-if="item.notes" class="text-gray-400 text-xs ml-1">
                    <FontAwesomeIcon icon="fal fa-sticky-note" class="text-gray-400" fixed-width aria-hidden="true" />
                    {{ item.notes }}
                </span>
            </div>
        </template> -->
    </Table>
</template>
