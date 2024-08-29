<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import { ref } from 'vue'
import { routeType } from "@/types/route"
import { cloneDeep } from "lodash"
import { Link } from '@inertiajs/vue3'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faShoppingBasket, faClock, faEllipsisV, } from '@far'
import { faSave } from '@fad'
import { faStickyNote, faClipboard, faInventory, faForklift, faSave as falSave } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'


library.add(faShoppingBasket, faStickyNote, faClock, faEllipsisV, faClipboard, faInventory, faForklift, falSave, faSave)

const props = defineProps<{
    data: object,
    locationRoute: routeType
    associateLocationRoute: routeType,
    disassociateLocationRoute: routeType,
    auditRoute: routeType,
    moveLocationRoute: routeType
}>();

const cloneData = ref(cloneDeep(props.data))

</script>


<template>
    <ul class="divide-y divide-gray-100 bg-white shadow-sm ring-1 ring-gray-900/5 ">
        <li v-for="(location, index) in cloneData.locations.data" :key="location.code"
            class="relative flex justify-between gap-x-6 px-4 py-4 hover:bg-gray-50 sm:px-6">

            <div class="flex items-center w-1/2 gap-x-4">
                <!-- Location Icon -->
                <FontAwesomeIcon class="h-3 w-3 flex-none rounded-full bg-gray-50" :icon="faStickyNote" />
                <FontAwesomeIcon class="h-5 w-5 flex-none rounded-full bg-gray-50" :icon="faShoppingBasket" />

                <div class="flex-auto">
                    <div class="text-sm font-semibold leading-6 text-gray-900">
                        {{ location.location.code }}
                        <span v-if="location.settings.min_stock || location.settings.max_stock" class="text-gray-400">
                            ( {{ location?.settings?.min_stock }} , {{ location?.settings?.max_stock }} )
                        </span>
                        <span v-else class="text-gray-400">( ? )</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Stock Information -->
            <div class="flex items-center w-1/4 gap-x-4">
                <div class="flex sm:flex-col sm:items-end">
                    <div class="flex gap-x-1">
                        <div class="flex-auto">
                            <div class="text-sm font-semibold leading-6 text-gray-900">999</div>
                        </div>
                        <FontAwesomeIcon class="h-4 w-4 mt-1 flex-none rounded-full bg-gray-50" :icon="faClock" />
                    </div>
                </div>
            </div>

            <!-- Right Side: Stock Information (Duplicated) -->
            <div class="flex justify-end w-1/4">
                <div class="flex justify-end gap-3">
                    <PureInputNumber v-model="location.quantity" minValue="0"  @input="(e)=>location.quantity =e"/>
                    <span class="ml-2 my-auto flex-shrink-0">
                        <Link v-if="data.locations.data[index].quantity != location.quantity" method="patch"
                            :href="route(auditRoute.name, { locationOrgStock: location.id })"
                            :data="{ quantity: location.quantity }" type="button">
                        <button class="h-9 align-bottom text-center" type="submit">
                            <FontAwesomeIcon :icon="faSave" class="h-8"
                                :style="{ '--fa-secondary-color': 'rgb(0, 255, 4)' }" aria-hidden="true" />
                        </button>


                        </Link>
                        <FontAwesomeIcon v-else icon="fal fa-save" class="h-8 text-gray-300" aria-hidden="true" />
                    </span>
                </div>
            </div>
        </li>
    </ul>
</template>