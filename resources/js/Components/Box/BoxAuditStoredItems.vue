<script setup lang='ts'>
import JsBarcode from 'jsbarcode'
import { inject, onMounted, ref } from 'vue'
import DatePicker from '@vuepic/vue-datepicker'
import { useFormatTime, useDaysLeftFromToday } from '@/Composables/useFormatTime'
import { PalletDelivery, BoxStats } from '@/types/Pallet'
import { capitalize } from '@/Composables/capitalize'
import { trans } from 'laravel-vue-i18n'
import Popover from '@/Components/Popover.vue'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'
import { Link, router } from '@inertiajs/vue3'

import { notify } from '@kyvg/vue3-notification'
import { routeType } from '@/types/route'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faQuestionCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import OrderSummary from '@/Components/Summary/OrderSummary.vue'

library.add(faQuestionCircle)

const props = defineProps<{
    dataPalletDelivery: PalletDelivery
    boxStats: BoxStats
}>()




onMounted(() => {
    JsBarcode('#palletDeliveryBarcode', 'pad-' + route().v().params.palletDelivery, {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: 50,
        displayValue: false
    })
})
</script>

<template>
    <div class="h-min grid grid-cols-2 sm:grid-cols-4 border-t border-b border-gray-200 divide-x divide-gray-300">
        <!-- Box: Customer -->
        <BoxStatPallet class="py-1 sm:py-2 px-3" :label="dataPalletDelivery.customer_name" icon="fal fa-user">
            <!-- Field: Reference -->
            <Link as="a" v-if="boxStats.fulfilment_customer.customer.reference"
                :href="route('grp.org.fulfilments.show.crm.customers.show', [route().params.organisation, boxStats.fulfilment_customer.fulfilment.slug, boxStats.fulfilment_customer.slug])"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer secondaryLink">
            <dt v-tooltip="'Company name'" class="flex-none">
                <span class="sr-only">Reference</span>
                <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                    aria-hidden='true' />
            </dt>
            <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="boxStats.fulfilment_customer.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer.customer.contact_name }}</dd>
            </div>


            <!-- Field: Company name -->
            <div v-if="boxStats.fulfilment_customer.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer.customer.company_name }}</dd>
            </div>

            <!-- Field: Email -->
            <div v-if="boxStats.fulfilment_customer?.customer.email" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Email</span>
                    <FontAwesomeIcon icon='fal fa-envelope' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500 white w-full truncate">{{ boxStats.fulfilment_customer?.customer.email }}</dd>
            </div>

            <!-- Field: Phone -->
            <div v-if="boxStats.fulfilment_customer?.customer.phone" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer?.customer.phone }}</dd>
            </div>
        </BoxStatPallet>


        <!-- Box: Status -->
        <BoxStatPallet class="py-1 sm:py-2 px-3" :label="capitalize(dataPalletDelivery.state)" icon="fal fa-truck-couch">
            <div class="mb-4 h-full w-full py-1 px-2 flex flex-col bg-gray-100 ring-1 ring-gray-300 rounded items-center">
                <svg id="palletDeliveryBarcode" class="w-full h-full" />
                <div class="text-xxs text-gray-500">
                    pad-{{ route().params.palletDelivery }}
                </div>
            </div>
            
            <!-- <pre>{{ dataPalletDelivery }}</pre> -->
            <div class="flex items-center w-full flex-none gap-x-2 mb-2">
                <dt class="flex-none">
                    <span class="sr-only">{{ boxStats.delivery_status.tooltip }}</span>
                    <FontAwesomeIcon :icon='boxStats.delivery_status.icon' :class='boxStats.delivery_status.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500" :class='boxStats.delivery_status.class'>{{
                    boxStats.delivery_status.tooltip }}</dd>
            </div>


            <!-- Stats: count Pallets, Services, Physical Goods -->
            <div class="border-t border-gray-300 mt-2 pt-2 space-y-0.5">
                <div v-tooltip="trans('Count of pallets')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-base font-medium tabular-nums">{{ dataPalletDelivery.number_pallets }} <span class="text-gray-400 font-normal">{{ dataPalletDelivery.number_pallets > 1 ? trans('Pallets') : trans('Pallet') }}</span></dd>
                </div>
            </div>
        </BoxStatPallet>


        <!-- Box: Order summary -->
        <BoxStatPallet class="sm:col-span-2 border-t sm:border-t-0 border-gray-300">
            <section aria-labelledby="summary-heading" class="rounded-lg px-4 py-4 sm:px-6 lg:mt-0">
            </section>
        </BoxStatPallet>
    </div>
</template>