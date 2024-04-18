<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { library } from "@fortawesome/fontawesome-svg-core"

import { faTimes } from '@fal'
import TagPallete from '@/Components/TagPallete.vue'
import Icon from '@/Components/Icon.vue'

library.add( faTimes )

const props = defineProps<{
    data: object,
    tab?: string
}>()


// function palletRoute(pallet: Pallet) {
//     switch (route().current()) {
//         case 'grp.org.fulfilments.show.operations.pallets.index':
//             return route(
//                 'grp.org.fulfilments.show.operations.pallets.show',
//                 [
//                     route().params['organisation'],
//                     route().params['fulfilment'],
//                     pallet['slug']
//                 ])
//         case 'grp.org.warehouses.show.fulfilment.pallets.index':
//             return route(
//                 'grp.org.warehouses.show.fulfilment.pallets.show',
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
            <Link :href="'palletRoute(pallet)'" class="specialUnderline">
                {{ pallet['reference'] }}
            </Link>
        </template> -->

        <template #cell(state)="{ item: pallet }">
            <!-- <Icon v-if="pallet['state_icon']" :data="pallet['state_icon']" class="px-1" /> -->
            <TagPallete :stateIcon="pallet.state_icon" />
        </template>
        <template #cell(type_icon)="{ item: pallet }">
			<Icon :data="pallet.type_icon" class="px-1" />
		</template>
    </Table>
</template>
