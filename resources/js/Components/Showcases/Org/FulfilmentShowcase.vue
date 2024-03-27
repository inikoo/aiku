<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import FlatTreeMap from "@/Components/Navigation/FlatTreeMap.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faNarwhal, faBusinessTime, faUserTie } from '@fal'
import { faTruck, faSignOut, faDollarSign } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faNarwhal, faBusinessTime, faUserTie, faTruck, faSignOut, faDollarSign)

import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js'
import { inject } from 'vue'
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

const props = defineProps<{
    data: {}
}>()

const layout = inject('layout')

const dataStats = {
    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    datasets: [
        {
            label: 'Dataset 1',
            data: [34, 55, 47, 33, 42, 55, 37, 45, 43, 54, 49, 53],
            borderColor: layout.app.theme[0],
            backgroundColor: layout.app.theme[3],
        },
        {
            label: 'Dataset 1',
            data: [37, 49, 35, 43, 40, 52, 44, 49, 40, 52, 55, 47],
            borderColor: layout.app.theme[2],
            backgroundColor: layout.app.theme[2],
        },
    ]
}

const config = {
    type: 'line',
    data: dataStats,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Chart.js Line Chart'
            }
        }
    },
}

const stats = [
    { id: 1, name: 'Total revenue', value: '$8,280' },
    { id: 2, name: 'Increased revenue', value: '13.7%' },
    { id: 3, name: 'Sales', value: '1,843' },
    { id: 4, name: 'Stocks', value: '319' },
]

</script>

<template>
    <!-- <pre>{{ data }}</pre> -->
    <FlatTreeMap class="mx-4" v-for="(treeMap, idx) in data.flatTreeMaps" :key="idx" :nodes="treeMap" />
    <div class="px-8 py-6">
        <div class="grid grid-cols-3 gap-x-4">
            <div class="col-span-2 flex flex-col gap-y-8">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-10 text-slate-600 sm:grid-cols-2 sm:gap-y-16 lg:mx-0 lg:max-w-none lg:grid-cols-4">
                    <div v-for="stat in stats" :key="stat.id" class="flex flex-col gap-y-3 border-l border-indigo-700/20 pl-6">
                        <dt class="text-sm leading-6">{{ stat.name }}</dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight" :style="{color: layout.app.theme[3]}">{{ stat.value }}</dd>
                    </div>
                </dl>

                <div class="">
                    <Line :data="dataStats" :options="config" />
                </div>
            </div>

            <!-- Section: Scheduled activities -->
            <div class="bg-slate-50 rounded-lg ring-1 ring-slate-300 px-4 py-6">
                <div class="font-semibold text-lg">Scheduled activities</div>
                <div class="text-slate-400">April 2024</div>
                <div class="mt-4">
                    <div class="flex items-center gap-x-3">
                        <div class="h-11 aspect-square bg-slate-200 text-slate-600 rounded-md flex justify-center items-center">
                            <FontAwesomeIcon icon='fas fa-truck' class='text-lg' fixed-width aria-hidden='true' />
                        </div>
                        <div class="flex flex-col">
                            <div class="font-semibold">Pallet Delivery</div>
                            <div class="text-slate-400 text-sm">1 day</div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center gap-x-3">
                        <div class="h-11 aspect-square bg-slate-200 text-slate-600 rounded-md flex justify-center items-center">
                            <FontAwesomeIcon icon='fas fa-sign-out' class='text-lg' fixed-width aria-hidden='true' />
                        </div>
                        <div class="flex flex-col">
                            <div class="font-semibold">Pallet Return</div>
                            <div class="text-slate-400 text-sm">2 days</div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center gap-x-3">
                        <div class="h-11 aspect-square bg-slate-200 text-slate-600 rounded-md flex justify-center items-center">
                            <FontAwesomeIcon icon='fas fa-dollar-sign' class='text-lg' fixed-width aria-hidden='true' />
                        </div>
                        <div class="flex flex-col">
                            <div class="font-semibold">Customer Payment</div>
                            <div class="text-slate-400 text-sm">4 days</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
