<script setup lang='ts'>
import { trans } from "laravel-vue-i18n"
import BoxStatPallet from "@/Components/Pallet/BoxStatPallet.vue"
import DatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { useFormatTime, useDaysLeftFromToday } from '@/Composables/useFormatTime'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'

import Popover from '@/Components/Popover.vue'
import { PalletDelivery, BoxStats, PalletReturn, PDRNotes } from '@/types/Pallet'
import Modal from '@/Components/Utils/Modal.vue'

import { inject, ref } from 'vue'
import { capitalize } from '@/Composables/capitalize'
import { routeType } from "@/types/route"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import RetinaBoxNote from "@/Components/Retina/Storage/RetinaBoxNote.vue"
import OrderSummary from "@/Components/Summary/OrderSummary.vue"
import ModalAddress from '@/Components/Utils/ModalAddress.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBuilding, faIdCardAlt, faMapMarkerAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faBuilding, faIdCardAlt, faMapMarkerAlt)


const props = defineProps<{
    box_stats: BoxStats
    data_pallet: PalletDelivery | PalletReturn
    updateRoute: {
        route: routeType
    }
    notes_data: {
        [key: string]: PDRNotes
    }
}>()

const layout = inject('layout', layoutStructure)


const isModalAddress = ref(false)

console.log('fff', props.box_stats)

// Method: On change estimated date
// const onChangeEstimateDate = async (close: Function) => {
//     try {
//         router.patch(
//             route(props.updateRoute.route.name, props.updateRoute.route.parameters),
//             {
//                 estimated_delivery_date: props.data_pallet.estimated_delivery_date
//             },
//             {
//                 onStart: () => isLoadingSetEstimatedDate.value = true,
//                 onError: () => {
//                     notify({
//                         title: "Failed",
//                         text: "Failed to update the Delivery date, try again.",
//                         type: "error",
//                     })
//                 },
//                 onSuccess: () => close(),
//                 onFinish: () => isLoadingSetEstimatedDate.value = false,
//             })
//     } catch (error) {
//         console.log(error)
//         notify({
//             title: "Failed",
//             text: "Failed to update the Delivery date, try again.",
//             type: "error",
//         })
//     }
// }

// const disableBeforeToday = (date: Date) => {
//     const today = new Date()
//     // Set time to 00:00:00 for comparison purposes
//     today.setHours(0, 0, 0, 0)
//     return date < today
// }
</script>

<template>
    <div class="h-min grid md:grid-cols-4 border-b border-gray-200 divide-y md:divide-y-0 divide-x divide-gray-200">
        <!-- Box: Detail -->
        <BoxStatPallet :color="{ bgColor: layout.app.theme[0], textColor: layout.app.theme[1] }" class=" pb-2 py-5 px-3"
            :tooltip="trans('Detail')" :label="capitalize(data_pallet.state)" icon="fal fa-truck-couch">

            <!-- Field: Reference -->
            <div as="a" v-if="box_stats.fulfilment_customer.customer.reference"
                class="flex items-center w-fit flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Reference</span>
                    <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.reference }}</dd>
            </div>

            <!-- Field: Contact name -->
            <div v-if="box_stats.fulfilment_customer.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.contact_name }}</dd>
            </div>


            <!-- Field: Company name -->
            <div v-if="box_stats.fulfilment_customer.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.company_name }}</dd>
            </div>
            
            <!-- Field: Delivery Address -->
            <div class="flex items-start w-full flex-none gap-x-2">
                <dt v-tooltip="`Pallet Return's address`" class="flex-none">
                    <span class="sr-only">Delivery address</span>
                    <FontAwesomeIcon icon='fal fa-map-marker-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>

                <dd v-if="box_stats.fulfilment_customer.address.value" class="text-xs text-gray-500">
                    <div class="relative px-2 py-1 ring-1 ring-gray-300 rounded bg-gray-50">
                        <span class="" v-html="box_stats.fulfilment_customer.address.value.formatted_address" />

                        <div @click="() => isModalAddress = true"
                            class="whitespace-nowrap select-none text-gray-500 hover:text-blue-600 underline cursor-pointer">
                            <span>Edit</span>
                        </div>
                    </div>
                </dd>
                
                <div v-else @click="() => isModalAddress = true" class="leading-6 text-xs inline whitespace-nowrap select-none text-gray-500 hover:text-blue-600 underline cursor-pointer">
                    Setup delivery address
                </div>
            </div>
        </BoxStatPallet>


        <!-- Box: Notes -->
        <BoxStatPallet :color="{ bgColor: layout.app.theme[0], textColor: layout.app.theme[1] }" class="pb-2 pt-6 px-3"
            :tooltip="trans('Notes')" :percentage="0">
            <div class="grid gap-y-3 mb-3">
                <RetinaBoxNote
                    :noteData="notes_data.return"
                    :updateRoute="updateRoute.route"
                />

            </div>
            
            <div class="border-t border-gray-300 pt-1">
                <div class="flex items-center w-full flex-none gap-x-2" 
                    :class='box_stats.delivery_status.class'>
                    <dt class="flex-none">
                        <span class="sr-only">{{ box_stats.delivery_status.tooltip }}</span>
                        <FontAwesomeIcon
                            :icon='box_stats.delivery_status.icon'
                            size="xs"
                            fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-xs">{{ box_stats.delivery_status.tooltip }}</dd>
                </div>
            </div>
        </BoxStatPallet>


        <!-- Box: Order summary -->
        <BoxStatPallet class="sm:col-span-2 border-t sm:border-t-0 border-gray-300">
            <section aria-labelledby="summary-heading" class="rounded-lg px-4 py-4 sm:px-6 lg:mt-0">
                <h2 id="summary-heading" class="text-lg font-medium">Order summary</h2>

                <OrderSummary :order_summary="box_stats.order_summary" />

                <!-- <div class="mt-6">
                    <button type="submit"
                        class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                </div> -->
            </section>
        </BoxStatPallet>
    </div>

    <Modal :isOpen="isModalAddress" @onClose="() => (isModalAddress = false)">
        <ModalAddress
            :addresses="box_stats.fulfilment_customer.address"
            :updateRoute="updateRoute.route"
        />
    </Modal>
</template>