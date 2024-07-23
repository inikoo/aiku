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
import { faQuestionCircle, faIdCardAlt, faEnvelope, faPhone, faCalendarDay } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import OrderSummary from '@/Components/Summary/OrderSummary.vue'

library.add(faQuestionCircle, faIdCardAlt, faEnvelope, faPhone, faCalendarDay)

const locale = inject('locale', {})
const props = defineProps<{
    boxStats: BoxStats
}>()
</script>

<template>
    <div class="h-min grid grid-cols-2 sm:grid-cols-4 border-t border-b border-gray-200 divide-x divide-gray-300">
        <!-- Box: Customer -->
        <BoxStatPallet class="py-1 sm:py-2 px-4" :label="'dataPalletDelivery.customer_name'" icon="fal fa-user">
            
            <!-- Field: Reference -->
            <Link as="a" v-if="boxStats.customer.data.customer.reference"
                :href="route('grp.org.fulfilments.show.crm.customers.show', [route().params.organisation, boxStats.customer.data.fulfilment.slug, boxStats.customer.data.slug])"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer secondaryLink">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Reference</span>
                    <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-sm text-gray-500">{{ boxStats.customer.data.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="boxStats.customer.data.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-sm text-gray-500">{{ boxStats.customer.data.customer.contact_name }}</dd>
            </div>


            <!-- Field: Company name -->
            <div v-if="boxStats.customer.data.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-sm text-gray-500">{{ boxStats.customer.data.customer.company_name }}</dd>
            </div>

            <!-- Field: Email -->
            <div v-if="boxStats.customer.data?.customer.email" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Email</span>
                    <FontAwesomeIcon icon='fal fa-envelope' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-sm text-gray-500 white w-full truncate">{{ boxStats.customer.data?.customer.email }}</dd>
            </div>

            <!-- Field: Phone -->
            <div v-if="boxStats.customer.data?.customer.phone" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-sm text-gray-500">{{ boxStats.customer.data?.customer.phone }}</dd>
            </div>
        </BoxStatPallet>


        <!-- Box: Status -->
        <BoxStatPallet class="py-1 sm:py-3 px-3" :label="capitalize('dataPalletDelivery.state')" icon="fal fa-truck-couch">
            <!-- <div>
                Stats
            </div> -->

            <!-- Stats: count Pallets, Services, Physical Goods -->
            <div class="space-y-0.5">
                <div v-tooltip="trans('Count of pallets')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-base font-medium tabular-nums">{{ locale.number(boxStats.stats.number_pallets) }} <span class="text-gray-400 font-normal">{{ boxStats.stats.number_pallets > 1 ? trans('Pallets') : trans('Pallet') }}</span></dd>
                </div>

                <div v-tooltip="trans('Count of stored item')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-concierge-bell' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-base font-medium tabular-nums">{{ locale.number(boxStats.stats.number_stored_items) }} <span class="text-gray-400 font-normal">{{ boxStats.stats.number_stored_items > 1 ? trans('Stored items') : trans('Stored item') }}</span></dd>
                </div>

                <!-- <div v-tooltip="trans('Count of physical goods')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-cube' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-base font-medium tabular-nums">{{ boxStats.stats.number_physical_goods }} <span class="text-gray-400 font-normal">{{ boxStats.stats.number_pallets > 1 ? trans('Physical goods') : trans('Physical good') }}</span></dd>
                </div> -->
            </div>
        </BoxStatPallet>


        <!-- Box: Order summary -->
        <BoxStatPallet class="sm:col-span-2 border-t sm:border-t-0 border-gray-300">
            <section aria-labelledby="summary-heading" class="rounded-lg px-4 py-4 sm:px-6 lg:mt-0">
                <!-- <h2 id="summary-heading" class="text-lg font-medium">Order summary</h2> -->

                <OrderSummary :order_summary="boxStats.order_summary" />

                <!-- <div class="mt-6">
                    <button type="submit"
                        class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                </div> -->
            </section>
        </BoxStatPallet>
    </div>
</template>