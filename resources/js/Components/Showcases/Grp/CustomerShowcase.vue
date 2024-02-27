<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Thu, 25 May 2023 15:03:05 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { useLayoutStore } from "@/Stores/layout"
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js'
import { Line } from 'vue-chartjs'
import PureRadio from '@/Components/Pure/PureRadio.vue'
import { ref } from 'vue'
import { useFormatTime } from '@/Composables/useFormatTime copy'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

const props = defineProps<{
    data: {
        customer: {
            slug: string
            reference: string
            name: string
            contact_name: string
            company_name?: string
            email?: string
            phone?: string
            created_at: Date
            updated_at: Date
            shop?: string
            shop_slug?: string
            number_active_clients?: number
        }
        fulfilment_customer: {
            number_pallets?: number
            number_pallets_state_received?: number
            number_stored_items?: number
            number_pallets_deliveries?: number
            number_pallets_returns?: number

        }
    },
    tab: string
}>()

const customerStats = [
    {
        title: 'Pallets',
        value: props.data.fulfilment_customer.number_pallets,
        icon: 'fal fa-pallet'
    },
    {
        title: 'Pallets been received',
        value: props.data.fulfilment_customer.number_pallets_state_received,
        icon: 'fal fa-pallet'
    },
    {
        title: 'Stored items',
        value: props.data.fulfilment_customer.number_stored_items,
        icon: 'fal fa-narwhal'
    },
    {
        title: 'Deliveries',
        value: props.data.fulfilment_customer.number_pallets_deliveries,
        icon: 'fal fa-truck-couch'
    },
    {
        title: 'Return',
        value: props.data.fulfilment_customer.number_pallets_returns,
        icon: 'fal fa-sign-out-alt '
    },
]

const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul']
// const dataChart = {
//     labels: labels,
//     datasets: [{
//         label: 'Bills',
//         data: [65, 78, 50, 71, 60, 85, 40],
//         fill: false,
//         borderColor: useLayoutStore().app.theme[2],
//         tension: 0.4
//     }]
// }

const abcdef = ref()
const optionRadio = [
    {
        value: 'palletstorage',
        name: 'Pallet Storage'
    },
    {
        value: 'itemsstorage',
        name: 'Items Storage'
    },
    {
        value: 'dropshopping',
        name: 'Dropshopping'
    },
]
</script>

<template>
    <div class="px-8 mt-4">
        <PureRadio v-model="abcdef" :options="optionRadio" mode="compact" />
    </div>

    <div class="px-4 py-5 md:px-6 lg:px-8 grid grid-cols-2 gap-x-3">
        <!-- Section: Profile box -->
        <div class="bg-gradient-to-tr from-slate-800 to-slate-700 text-white p-6 flex flex-col justify-between rounded-md overflow-hidden">
            <div class="w-full">
                <h2 class="text-3xl font-bold">{{ data.customer.name }}</h2>
                <div class="text-lg">
                    {{ data.customer.shop }}<span class="text-gray-400">({{ data.customer.number_active_clients || 0 }} clients)</span>
                </div>
            </div>

            <div class="space-y-3 text-sm text-gray-100">
                <div class="border-l-2 border-gray-500 pl-4">
                    <h3 class="font-bold">Phone</h3>
                    <address class="not-italic text-gray-300">
                        <p>{{ data.customer.phone || '-' }}</p>
                    </address>
                </div>
                <div class="border-l-2 border-gray-500 pl-4">
                    <h3 class="font-bold">Email</h3>
                    <address class="not-italic text-gray-300">
                        <p>{{ data.customer.email || '-' }}</p>
                    </address>
                </div>
                <div class="border-l-2 border-gray-500 pl-4">
                    <h3 class="font-bold">Member since</h3>
                    <address class="not-italic text-gray-300">
                        <p>{{ useFormatTime(data.customer.created_at) || '-' }}</p>
                    </address>
                </div>
            </div>
        </div>

        <!-- Section: Stats box -->
        <div class="grid grid-cols-2 gap-y-2 gap-x-2 text-gray-600">
            <div v-for="stat in customerStats" class="border border-gray-50 rounded p-3" :style="{
                border: `1px solid ${useLayoutStore().app.theme[4] + '22'}`
            }">
                <div class="flex justify-between mb-1">
                    <div>
                        <span class="block text-gray-400 font-medium mb-2">{{ stat.title }}</span>
                        <div class="font-bold text-2xl">{{ stat.value || 0 }}</div>
                    </div>
                    <div class="h-10 aspect-square flex items-center justify-center rounded" :style="{
                        backgroundColor: useLayoutStore().app?.theme[2] + '22',
                        color: useLayoutStore().app.theme[2]
                    }">
                        <FontAwesomeIcon :icon='stat.icon' class='' fixed-width aria-hidden='true' />
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

