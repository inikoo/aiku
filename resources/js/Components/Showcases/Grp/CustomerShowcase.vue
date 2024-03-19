<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Thu, 25 May 2023 15:03:05 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { ref } from 'vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import CustomerShowcaseStats from '@/Components/Showcases/Grp/CustomerShowcaseStats.vue'

import { routeType } from '@/types/route'
import { PalletCustomer, PieCustomer } from '@/types/Pallet'
import { trans } from 'laravel-vue-i18n'
import TabSelector from '@/Components/Elements/TabSelector.vue'

const props = defineProps<{
    data: {
        customer: PalletCustomer
        fulfilment_customer: {
            radioTabs: {
                [key: string]: boolean
            }
            number_pallets?: number
            number_pallets_state_received?: number
            number_stored_items?: number
            number_pallets_deliveries?: number
            number_pallets_returns?: number
        }
        updateRoute: routeType
        pieData: {
            [key: string]: PieCustomer
        }
    },
    tab: string
}>()

// Tabs radio: v-model
const radioValue = ref<string[]>(Object.keys(props.data.fulfilment_customer.radioTabs).filter(key => props.data.fulfilment_customer.radioTabs[key]))

// Tabs radio: options
const optionRadio = [
    {
        value: 'pallets_storage',
        label: 'Pallet Storage'
    },
    {
        value: 'items_storage',
        label: 'Items Storage'
    },
    {
        value: 'dropshipping',
        label: 'Dropshipping'
    },
]

</script>

<template>

    <!-- Section: Radio -->
    <div class="px-8 mt-4">
        <TabSelector :optionRadio="optionRadio" :radioValue="radioValue" :updateRoute="data.updateRoute"/>
    </div>

    <!-- Section: Stats box -->
    <div class="px-4 py-5 md:px-6 lg:px-8 grid grid-cols-2 gap-x-4">
        <!-- Box Group: Profile -->
        <div class="border border-slate-200 text-retina-600 p-6 flex flex-col justify-between rounded-lg shadow overflow-hidden">
            <div class="w-full">
                <h2 v-if="data.customer?.name" class="text-3xl font-bold">{{ data.customer?.name }}</h2>
                <h2 v-else class="text-3xl font-light italic brightness-75">{{ trans('No name') }}</h2>
                <div class="text-lg">
                    {{ data.customer?.shop }}
                    <span class="text-gray-400">
                        ({{ data.customer?.number_active_clients || 0 }} clients)
                    </span>
                </div>
            </div>
            <div class="space-y-3 text-sm text-slate-500">
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-light">Phone</h3>
                    <address class="text-base font-bold not-italic text-slate-700">
                        <p>{{ data.customer?.phone || '-' }}</p>
                    </address>
                </div>
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-light">Email</h3>
                    <address class="text-base font-bold not-italic text-slate-700">
                        <p>{{ data.customer?.email || '-' }}</p>
                    </address>
                </div>
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-light">Member since</h3>
                    <address class="text-base font-bold not-italic text-slate-700">
                        <p>{{ useFormatTime(data.customer?.created_at) || '-' }}</p>
                    </address>
                </div>
            </div>
        </div>

        <!-- Box Group: Pallets -->
        <CustomerShowcaseStats :pieData="data.pieData"/>
    </div>
</template>

