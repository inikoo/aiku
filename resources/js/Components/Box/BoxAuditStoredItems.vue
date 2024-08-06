<script setup lang='ts'>
import JsBarcode from 'jsbarcode'
import { inject, onMounted, ref } from 'vue'
import { PalletDelivery, BoxStats } from '@/types/Pallet'
import { capitalize } from '@/Composables/capitalize'
import { trans } from 'laravel-vue-i18n'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'
import { Link, router } from '@inertiajs/vue3'



import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faQuestionCircle, faEnvelope, faIdCardAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'

library.add(faQuestionCircle, faIdCardAlt, faEnvelope)

const props = defineProps<{
    auditData: PalletDelivery
    boxStats: BoxStats
}>()


console.log('ini',props)

onMounted(() => {
    JsBarcode('#palletDeliveryBarcode', route().v().params.palletDelivery, {
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
        <BoxStatPallet class="py-1 sm:py-2 px-3" :label="boxStats.customer.contact_name" icon="fal fa-user">
            <!-- Field: Reference -->
            <Link as="a" v-if="boxStats?.customer?.reference"
                :href="route('grp.org.fulfilments.show.crm.customers.show', [route().params.organisation, boxStats.fulfilment.slug, boxStats.slug])"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer secondaryLink">
            <dt v-tooltip="'Company name'" class="flex-none">
                <span class="sr-only">Reference</span>
                <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                    aria-hidden='true' />
            </dt>
            <dd class="text-xs text-gray-500">{{ boxStats.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="boxStats.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.customer.contact_name }}</dd>
            </div>


            <!-- Field: Company name -->
            <div v-if="boxStats.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.customer.company_name }}</dd>
            </div>

            <!-- Field: Email -->
            <div v-if="boxStats?.customer.email" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Email</span>
                    <FontAwesomeIcon icon='fal fa-envelope' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500 white w-full truncate">{{ boxStats?.customer.email }}</dd>
            </div>

            <!-- Field: Phone -->
            <div v-if="boxStats?.customer.phone" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats?.customer.phone }}</dd>
            </div>
        </BoxStatPallet>


        <!-- Box: Status -->
        <BoxStatPallet class="py-1 sm:py-2 px-3" :label="capitalize(auditData.reference)" icon="fal fa-truck-couch">
            <div class="mb-4 h-full w-full py-1 px-2 flex flex-col bg-gray-100 ring-1 ring-gray-300 rounded items-center">
                <svg id="palletDeliveryBarcode" class="w-full h-full" />
                <div class="text-xxs text-gray-500">
                    pad-{{ auditData.reference }}
                </div>
            </div>

            <div class="flex items-center w-full flex-none gap-x-2 mb-2">
                <dt class="flex-none">
                    <span class="sr-only">{{ auditData.state_icon.tooltip }}</span>
                    <FontAwesomeIcon :icon='auditData.state_icon.icon' :class='auditData.state_icon.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500" :class='auditData.state_icon.class'>{{
                    auditData.state_icon.tooltip }}</dd>
            </div>
            


            <!-- Stats: count Pallets -->
            <div class="border-t border-gray-300 mt-2 pt-2 space-y-0.5">
                <div v-tooltip="trans('Count of pallets')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-base font-medium tabular-nums">{{ boxStats.number_pallets }} <span class="text-gray-400 font-normal">{{ boxStats.number_pallets > 1 ? trans('Pallets') : trans('Pallet') }}</span></dd>
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