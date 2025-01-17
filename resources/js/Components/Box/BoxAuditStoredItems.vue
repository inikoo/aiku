<script setup lang='ts'>
import JsBarcode from 'jsbarcode'
import { inject, onMounted, ref } from 'vue'
import { PalletDelivery, BoxStats } from '@/types/Pallet'
import { capitalize } from '@/Composables/capitalize'
import { trans } from 'laravel-vue-i18n'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'
import { Link, router } from '@inertiajs/vue3'



import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faNarwhal, faPallet, faQuestionCircle, faEnvelope, faPhone, faIdCardAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'

library.add(faNarwhal, faPallet, faQuestionCircle, faIdCardAlt, faEnvelope, faPhone)

const props = defineProps<{
    auditData: {}
    boxStats: BoxStats
}>()

const locale = inject('locale', aikuLocaleStructure)

console.log('ini',props)

onMounted(() => {
    JsBarcode('#palletDeliveryBarcode', route().v().params.storedItemAudit, {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: 50,
        displayValue: true
    })
})

const dataBoxStats = [
    {
        label: 'Audited',
        icon: 'fal fa-pallet',
        tooltip: trans('Pallet'),
        value: props.auditData.number_audited_pallets
    },
    {
        label: 'Audited',
        icon: 'fal fa-narwhal',
        tooltip: trans('Customer\'s SKU'),
        value: props.auditData.number_audited_stored_items
    },
    {
        label: 'Audited (with additions)',
        icon: 'fal fa-narwhal',
        tooltip: trans('Customer\'s SKU'),
        value: props.auditData.number_audited_stored_items_with_additions
    },
    {
        label: 'Audited (with subtractions)',
        icon: 'fal fa-narwhal',
        tooltip: trans('Customer\'s SKU'),
        value: props.auditData.number_audited_stored_items_with_with_subtractions
    },
    {
        label: 'Audited (with stock checked)',
        icon: 'fal fa-narwhal',
        tooltip: trans('Customer\'s SKU'),
        value: props.auditData.number_audited_stored_items_with_with_stock_checked
    },
    {
        label: 'Associated',
        icon: 'fal fa-narwhal',
        tooltip: trans('Customer\'s SKU'),
        value: props.auditData.number_associated_stored_items
    },
    {
        label: 'Created',
        icon: 'fal fa-narwhal',
        tooltip: trans('Customer\'s SKU'),
        value: props.auditData.number_created_stored_items
    },
]
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
                <dd class="text-base text-gray-500">{{ boxStats.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="boxStats.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-base text-gray-500">{{ boxStats.customer.contact_name }}</dd>
            </div>


            <!-- Field: Company name -->
            <div v-if="boxStats.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-base text-gray-500">{{ boxStats.customer.company_name }}</dd>
            </div>

            <!-- Field: Email -->
            <div v-if="boxStats?.customer.email" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Email</span>
                    <FontAwesomeIcon icon='fal fa-envelope' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <a :href="`mailto:${boxStats?.customer.email}`" class="text-base text-gray-500 hover:underline white w-full truncate">{{ boxStats?.customer?.email }}</a>
            </div>

            <!-- Field: Phone -->
            <div v-if="boxStats?.customer.phone" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <a :href="`tel:${boxStats?.customer.phone}`" class="text-base text-gray-500 hover:underline white w-full truncate">{{ boxStats?.customer?.phone }}</a>
            </div>
        </BoxStatPallet>


        <!-- Box: Status -->
        <BoxStatPallet class="py-1 sm:py-2 px-3" :label="capitalize(auditData.reference)" icon="fal fa-truck-couch">
            

            <div class="flex items-center w-full flex-none gap-x-2 mb-2">
                <dt class="flex-none">
                    <span class="sr-only">{{ auditData.state_icon.tooltip }}</span>
                    <FontAwesomeIcon :icon='auditData.state_icon.icon' :class='auditData.state_icon.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-base text-gray-500" :class='auditData.state_icon.class'>{{
                    auditData.state_icon.tooltip }}</dd>
            </div>
            
            <div class="mb-4 h-full w-full py-1 px-2 flex flex-col border-t border-gray-300 items-center">
                <svg id="palletDeliveryBarcode" class="w-full h-full" ></svg>
                <!-- <div class="text-xxs text-gray-500">
                    pad-{{ auditData.reference }}
                </div> -->
            </div>

            <!-- Stats: count Pallets -->
            <!-- <div class="border-t border-gray-300 mt-2 pt-2 space-y-0.5">
                <div v-tooltip="trans('Count of pallets')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-base font-medium tabular-nums">{{ boxStats.number_pallets }} <span class="text-gray-400 font-normal">{{ boxStats.number_pallets > 1 ? trans('Pallets') : trans('Pallet') }}</span></dd>
                </div>
            </div> -->
        </BoxStatPallet>


        <!-- Box: Order summary -->
        <BoxStatPallet class="sm:col-span-2 border-t sm:border-t-0 border-gray-300">
            <dl class="flex flex-col gap-y-2 text-gray-500 rounded-lg px-4 py-2">
                <div class="pt-2 first:pt-0 pr-2 flex flex-col gap-y-1.5 ">
                    <div v-for="stat in dataBoxStats" class="grid grid-cols-7 gap-x-4 items-center justify-between">
                        <dt class="col-span-5 flex items-center gap-x-1.5">
                            <FontAwesomeIcon v-if="stat.icon" v-tooltip="stat.tooltip" :icon='stat.icon' class='text-gray-400 text-sm' fixed-width aria-hidden='true' />
                            <span>{{stat.label}}</span>
                        </dt>

                        <!-- <Transition name="spin-to-down">
                            <dd :key="fieldSummary.quantity" class="justify-self-end">{{ typeof fieldSummary.quantity === 'number' ? locale.number(0) : null}}</dd>
                            ddd
                        </Transition> -->
                        
                        <div class="relative col-span-2 justify-self-end font-medium overflow-hidden">
                            <Transition name="spin-to-right">
                                <!-- <dd :key="fieldSummary.price_total" class="" :class="fieldSummary.price_total === 'free' ? 'text-green-600 animate-pulse' : ''">{{ locale.currencyFormat('usd', fieldSummary.price_total || 0) }}</dd> -->
                                {{stat.value}}
                            </Transition>
                        </div>
                    </div>
                </div>

                <!-- <pre>{{ order_summary }}</pre> -->
            </dl>
        </BoxStatPallet>
    </div>
</template>