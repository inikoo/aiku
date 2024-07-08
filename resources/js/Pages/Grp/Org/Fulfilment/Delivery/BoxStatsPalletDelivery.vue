<script setup lang='ts'>
import JsBarcode from 'jsbarcode'
import { onMounted, ref } from 'vue'
import DatePicker from '@vuepic/vue-datepicker'
import { useFormatTime, useDaysLeftFromToday } from '@/Composables/useFormatTime'
import { PalletDelivery, BoxStats } from '@/types/Pallet'
import { capitalize } from '@/Composables/capitalize'
import { trans } from 'laravel-vue-i18n'
import Popover from '@/Components/Popover.vue'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'
import { Link, router } from '@inertiajs/vue3'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { notify } from '@kyvg/vue3-notification'
import { routeType } from '@/types/route'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'

const props = defineProps<{
    dataPalletDelivery: PalletDelivery
    boxStats: BoxStats
    updateRoute: routeType
}>()


const disableBeforeToday = (date: Date) => {
    const today = new Date()
    // Set time to 00:00:00 for comparison purposes
    today.setHours(0, 0, 0, 0)
    return date < today
}

const isLoadingSetEstimatedDate = ref(false)
const onChangeEstimateDate = async (close: Function) => {
    router.patch(route(props.updateRoute.name, props.updateRoute.parameters),
    {
        estimated_delivery_date : props.dataPalletDelivery.estimated_delivery_date
    },
    {
        onStart: () => isLoadingSetEstimatedDate.value = true,
        onError: () => {
            notify({
                title: "Failed",
                text: "Failed to update the Delivery date, try again.",
                type: "error",
            })
        },
        onSuccess: () => close(),
        onFinish: () => isLoadingSetEstimatedDate.value = false,
    })
}

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

            <!-- Set estimated date -->
            <div class="flex items-center w-full gap-x-2">
                <dt v-tooltip="'Estimated received date'" class="flex-none">
                    <span class="sr-only">{{ boxStats.delivery_status.tooltip }}</span>
                    <FontAwesomeIcon :icon="['fal', 'calendar-day']" class="text-gray-400" fixed-width
                        aria-hidden='true' />
                </dt>

                <div v-if="dataPalletDelivery.state !== 'in-process'">
                    <dd class="text-xs text-gray-500">
                        {{
                            dataPalletDelivery.estimated_delivery_date
                            ? useFormatTime(dataPalletDelivery?.estimated_delivery_date)
                            : 'Not Set'
                        }}
                    </dd>
                </div>

                <Popover v-else position="">
                    <template #button>
                        <div v-if="dataPalletDelivery.estimated_delivery_date"
                            v-tooltip="useDaysLeftFromToday(dataPalletDelivery.estimated_delivery_date)"
                            class="group text-xs text-gray-500">
                            {{ useFormatTime(dataPalletDelivery?.estimated_delivery_date) }}
                            <FontAwesomeIcon icon='fal fa-pencil' size="sm"
                                class='text-gray-400 group-hover:text-gray-600' fixed-width aria-hidden='true' />
                        </div>

                        <div v-else class="text-xs text-gray-500 hover:text-gray-600 underline">
                            {{ trans('Set estimated date') }}
                        </div>
                    </template>

                    <template #content="{ close }">
                        <DatePicker
                            v-model="dataPalletDelivery.estimated_delivery_date"
                            @update:modelValue="() => onChangeEstimateDate(close)"
                            inline auto-apply
                            :disabled-dates="disableBeforeToday"
                            :enable-time-picker="false"
                        />
                        <div v-if="isLoadingSetEstimatedDate" class="absolute inset-0 bg-white/70 flex items-center justify-center">
                            <LoadingIcon class="text-5xl" />
                        </div>
                    </template>
                </Popover>
            </div>
        </BoxStatPallet>


        <!-- Box: Stats -->
        <BoxStatPallet class="py-1 sm:py-2 px-3 border-t sm:border-t-0 border-gray-300" :percentage="0">
            <div v-tooltip="trans('Total Pallet')" class="flex items-end w-fit pr-2 gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Total pallet</span>
                    <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium ">{{ dataPalletDelivery.number_pallets || 0 }}</dd>
            </div>

            <div v-tooltip="trans('Total Services')" class="flex items-end w-fit pr-2 gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Services</span>
                    <FontAwesomeIcon icon='fal fa-concierge-bell' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium">{{ dataPalletDelivery.number_services }}</dd>
            </div>

            <div v-tooltip="trans('Total Physical Goods')" class="flex items-end w-fit pr-2 gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Physical Goods</span>
                    <FontAwesomeIcon icon='fal fa-cube' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium">{{ dataPalletDelivery.number_physical_goods }}</dd>
            </div>

        </BoxStatPallet>


        <!-- Box: Barcode -->
        <BoxStatPallet class="border-t sm:border-t-0 border-gray-300">
            <div class="h-full w-full px-2 flex flex-col items-center isolate">
                <svg id="palletDeliveryBarcode" class="w-full" />
                <div class="text-xxs md:text-xxs text-gray-500 -mt-1 z-10">
                    pad-{{ route().params.palletDelivery }}
                </div>
            </div>
        </BoxStatPallet>
    </div>
</template>