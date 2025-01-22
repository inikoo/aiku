<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:46:51 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import TagPallet from '@/Components/TagPallet.vue'
import {useFormatTime} from "@/Composables/useFormatTime"
import { PalletDelivery } from "@/types/pallet-delivery"
import Icon from "@/Components/Icon.vue"
import { inject } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"

library.add(faPlus)

const props = defineProps<{
    data: {}
    tab?: string
}>()

const layout = inject('layout', layoutStructure)

// function palletReturnRoute(palletReturn: PalletDelivery) {
//     switch (route().current()) {
//         case 'grp.org.warehouses.show.dispatching.pallet-returns.index':
//             return route(
//                 'grp.org.warehouses.show.dispatching.pallet-returns.show',
//                 [
//                     route().params['organisation'],
//                     route().params['warehouse'],
//                     palletReturn.slug
//                 ]);
//         case 'grp.org.fulfilments.show.operations.pallet-returns.index':
//             return route(
//                 'grp.org.fulfilments.show.operations.pallet-returns.show',
//                 [
//                     route().params['organisation'],
//                     route().params['fulfilment'],
//                     palletReturn.slug
//                 ]);
//         case 'retina.fulfilment.storage.pallet_returns.index':
//             return route(
//                 'retina.fulfilment.storage.pallet_returns.show',
//                 [
//                     palletReturn.slug
//                 ]);
//         default:
//             return route(
//                 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.show',
//                 [
//                     route().params['organisation'],
//                     route().params['fulfilment'],
//                     route().params['fulfilmentCustomer'],
//                     palletReturn.slug
//                 ]);
//     }
// }

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(uploaded_at)="{ item: upload }">
            {{ useFormatTime(upload.uploaded_at) }}
        </template>
    </Table>
</template>
