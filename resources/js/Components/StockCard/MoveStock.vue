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
import Select from '@/Components/Forms/Fields/Select.vue'
import Popover from '@/Components/Popover.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import Button from "@/Components/Elements/Buttons/Button.vue";
import { notify } from "@kyvg/vue3-notification"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faShoppingBasket, faClock, faEllipsisV, } from '@far'
import { faForklift } from '@fad'
import { faStickyNote, faClipboard, faInventory, faForklift as falForklift } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'


library.add(faShoppingBasket, faStickyNote, faClock, faEllipsisV, faClipboard, faInventory, faForklift, falForklift)

const props = defineProps<{
    data: object,
    locationRoute: routeType
    associateLocationRoute: routeType,
    disassociateLocationRoute: routeType,
    auditRoute: routeType,
    moveLocationRoute: routeType
}>();


const cloneData = ref(cloneDeep(props.data))
const loading = ref(false)
const form = useForm({
    newLocation: null
})

const sendMoveStock = (location = null, realQty = 0, close = () => null) => {
    router.patch(route(props.moveLocationRoute.name, { locationOrgStock: location.id, targetLocation: form.newLocation }),
        { quantity: realQty - location.quantity },
        {
            onBefore: () => { loading.value = true },
            onSuccess: () => {
                form.reset('newLocation'),
                    loading.value = false,
                    close()
            },
            onError: () => {
                notify({
                    title: "Failed",
                    text: "failed to add location",
                    type: "error"
                })
                loading.value = false
            }

        })
}


watch(
    () => props.data,
    (newData) => {
        cloneData.value = cloneDeep(newData);
    },
    { deep: true }  // Add this option if you want to watch deeply nested changes
)

</script>


<template>
    <ul class="divide-y divide-gray-100 n bg-white shadow-sm ring-1 ring-gray-900/5 ">
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
                <div class="flex justify-end">
                    <div>
                        <PureInputNumber v-model="location.quantity" :maxValue="data.locations.data[index].quantity" />
                        <p v-if="data.locations.data[index].quantity != location.quantity"
                            class="text-orange-400 text-xs mt-2">stock to be moved : {{
                                data.locations.data[index].quantity -
                                location.quantity }}</p>
                    </div>


                    <span class="ml-2 my-auto flex-shrink-0">
                        <Popover v-if="data.locations.data[index].quantity != location.quantity">
                            <template #button="{ open, close }">
                                <button class="h-6 my-auto align-bottom text-center" type="submit">
                                    <FontAwesomeIcon :icon="faForklift" class="h-6"
                                        :style="{ '--fa-secondary-color': 'rgb(0, 255, 4)' }" aria-hidden="true" />
                                </button>
                            </template>

                            <template #content="{ open, close }">
                                <div class="w-72">
                                    <div class="mb-3">
                                        Move {{ data.locations.data[index].quantity -
                                            location.quantity }} stock to :
                                    </div>
                                    <div class="mb-3">
                                        <Select :form="form" :fieldName="'newLocation'" :options="data.locations.data"
                                            :fieldData="{
                                                placeholder: 'select Location',
                                                searchable: true,
                                                label: 'code',
                                                valueProp: 'id'
                                            }" />
                                    </div>
                                    <div class="flex justify-end">
                                        <Button :loading="loading" type="save"
                                            @click="() => sendMoveStock( location, data.locations.data[index].quantity ,close)" />
                                    </div>
                                </div>
                            </template>
                        </Popover>
                        <FontAwesomeIcon v-else :icon="falForklift" class="h-6 text-gray-300" aria-hidden="true" />
                    </span>
                </div>
            </div>
        </li>
    </ul>
</template>