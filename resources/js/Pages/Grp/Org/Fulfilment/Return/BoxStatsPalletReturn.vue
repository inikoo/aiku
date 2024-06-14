<!--
  -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
  
<script setup lang='ts'>
import JsBarcode from 'jsbarcode'
import { onMounted } from 'vue'
import { capitalize } from '@/Composables/capitalize'

import { PalletReturn, BoxStats } from '@/types/Pallet'
import { Link } from '@inertiajs/vue3'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const props = defineProps<{
    dataPalletReturn: PalletReturn
    boxStats: BoxStats
}>()


onMounted(() => {
    JsBarcode('#palletReturnBarcode', 'par-' + route().v().params.palletDelivery, {
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
        <BoxStatPallet class="py-1 sm:py-2 px-3">
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
            <div v-if="boxStats.fulfilment_customer?.customer.email"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Email</span>
                    <FontAwesomeIcon icon='fal fa-envelope' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500 w-full pr-4 break-words leading-none">ddddddd{{ boxStats.fulfilment_customer?.customer.email }}</dd>
            </div>

            <!-- Field: Phone -->
            <div v-if="boxStats.fulfilment_customer?.customer.phone"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer?.customer.phone }}</dd>
            </div>

            <!-- Field: Location -->
            <div v-if="boxStats.fulfilment_customer?.customer?.location?.length"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Location</span>
                    <FontAwesomeIcon icon='fal fa-map-marker-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer?.customer.location.join(", ") }}</dd>
            </div>
        </BoxStatPallet>


        <BoxStatPallet class="py-1 sm:py-2 px-3" :label="capitalize(dataPalletReturn.state)" icon="fal fa-truck-couch">
            <div class="flex items-center w-full flex-none gap-x-2">
                <dt class="flex-none">
                    <span class="sr-only">{{ boxStats.delivery_status.tooltip }}</span>
                    <FontAwesomeIcon :icon='boxStats.delivery_status.icon' :class='boxStats.delivery_status.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.delivery_status.tooltip }}</dd>
            </div>
        </BoxStatPallet>

        <!-- Box: Pallet -->
        <BoxStatPallet class="py-1 sm:py-2 px-3 border-t sm:border-t-0 border-gray-300" :percentage="0">
            <div class="flex items-end gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Total pallet</span>
                    <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium ">{{ dataPalletReturn.number_pallets }}</dd>
            </div>

            <div class="flex items-end gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Services</span>
                    <FontAwesomeIcon icon='fal fa-concierge-bell' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium">{{ dataPalletReturn.number_pallets }}</dd>
            </div>

            <div class="flex items-end gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Physical Goods</span>
                    <FontAwesomeIcon icon='fal fa-cube' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium">{{ dataPalletReturn.number_pallets }}</dd>
            </div>

        </BoxStatPallet>


        <!-- Box: Barcode -->
        <BoxStatPallet class="border-t sm:border-t-0 border-gray-300">
            <div class="h-full w-full px-2 flex flex-col items-center -mt-2">
                <svg id="palletReturnBarcode" class="w-full" />
                <div class="text-xxs md:text-xxs text-gray-500 -mt-1">
                    par-{{ route().params.palletReturn }}
                </div>
            </div>
        </BoxStatPallet>
    </div>
</template>