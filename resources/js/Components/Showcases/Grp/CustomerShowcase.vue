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
import * as chartConfig from './chartConfig.js'

ChartJS.register( CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend )

const props = defineProps<{
    data: {
        blueprint: {
            title: string
            icon: Array<string>
        },
        current: string,

        contactCard: {
        },
        stats: [{
            label: string,
            value: number,
        }],
    },
}>()

const customerStats = [
    {
        title: 'Pallets',
        value: 152,
        icon: 'fal fa-pallet'
    },
    {
        title: 'Stored items',
        value: 1956,
        icon: 'fal fa-narwhal'
    },
    {
        title: 'Deliveries',
        value: 152,
        icon: 'fal fa-truck-couch'
    },
    {
        title: 'Return',
        value: 10,
        icon: 'fal fa-sign-out-alt '
    },
]

const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul']
const data = {
    labels: labels,
    datasets: [{
        label: 'Bills',
        data: [65, 78, 50, 71, 60, 85, 40],
        fill: false,
        borderColor: useLayoutStore().app.theme[2],
        tension: 0.3
    }]
}

const config = {
    type: 'line',
    data: data,
};
</script>

<template >
    <div class="px-4 py-5 md:px-6 lg:px-8 grid grid-cols-2 gap-x-3">
        <div class="h-full w-full bg-slate-800 rounded text-white p-4">
            <div class="text-gray-400">Bills (2024)</div>
            <div class="text-4xl">$<span class="text-white font-bold">449</span></div>
            <div class="">
                <Line :data="data" :options="{ responsive: true, maintainAspectRatio: false }" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-y-2 gap-x-2 text-gray-600">
            <div v-for="stat in customerStats" class="border border-gray-50 rounded p-3"
                :style="{
                    border: `1px solid ${useLayoutStore().app.theme[4] + '22'}`
                }"
            >
                <div class="flex justify-between mb-1">
                    <div>
                        <span class="block text-gray-400 font-medium mb-2">{{ stat.title }}</span>
                        <div class="font-bold text-2xl">{{ stat.value }}</div>
                    </div>
                    <div class="h-10 aspect-square flex items-center justify-center rounded"
                        :style="{
                            backgroundColor: useLayoutStore().app?.theme[2] + '22',
                            color: useLayoutStore().app.theme[2]
                        }"
                    >
                        <FontAwesomeIcon :icon='stat.icon' class='' fixed-width aria-hidden='true' />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

