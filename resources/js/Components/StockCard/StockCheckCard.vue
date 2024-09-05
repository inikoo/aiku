<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import { ref, watch } from 'vue'
import { routeType } from "@/types/route"
import { cloneDeep } from "lodash"
import { Link } from '@inertiajs/vue3'
import InfoCard from '@/Components/StockCard/InfoCard.vue'
import {stockLocation} from "@/types/StockLocation"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faShoppingBasket, faClock, faEllipsisV, } from '@far'
import { faSave, faSpinnerThird } from '@fad'
import { faStickyNote, faClipboard, faInventory, faForklift, faSave as falSave } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'


library.add(faShoppingBasket, faStickyNote, faClock, faEllipsisV, faClipboard, faInventory, faForklift, falSave, faSave, faSpinnerThird)

const props = defineProps<{
    data: stockLocation,
    locationRoute: routeType
    associateLocationRoute: routeType,
    disassociateLocationRoute: routeType,
    auditRoute: routeType,
    moveLocationRoute: routeType
    updateLocationRoute :  routeType
}>();

const cloneData = ref(cloneDeep(props.data))

watch(
    () => props.data,
    (newData) => {
        cloneData.value = cloneDeep(newData);
    },
    { deep: true }
)
</script>


<template>
    <InfoCard v-bind="{...props, data : cloneData}">
        <template #Quantity="{ itemData : location, index }">
            <div class="flex justify-end w-1/4">
                <div class="flex justify-end gap-3">
                    <PureInputNumber v-model="location.quantity" minValue="0"  @input="(e)=>location.quantity =e"/>
                    <span class="ml-2 my-auto flex-shrink-0">
                        <Link v-if="data.locations.data[index].quantity != location.quantity" method="patch"
                            :href="route(auditRoute.name, { locationOrgStock: location.id })"
                            :data="{ quantity: location.quantity }" type="button" @onBefore="location.loading = true">
                        <button class="h-9 align-bottom text-center" type="submit">
                            <FontAwesomeIcon v-if="location.loading" icon='fad fa-spinner-third' class='text-2xl animate-spin' fixed-width aria-hidden='true' />
                            <FontAwesomeIcon v-else :icon="faSave" class="h-8"
                                :style="{ '--fa-secondary-color': 'rgb(0, 255, 4)' }" aria-hidden="true" />
                        </button>
                        </Link>
                        <FontAwesomeIcon v-else icon="fal fa-save" class="h-8 text-gray-300" aria-hidden="true" />
                    </span>
                </div>
            </div>
        </template>
    </InfoCard>
</template>