<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 18 Jan 2024 15:36:09 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useLayoutStore } from '@/Stores/layout'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler, ScriptableContext  } from 'chart.js'
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const layout = useLayoutStore()

const tabs = [
    { name: 'My Account', href: '#', current: true },
    { name: 'Company', href: '#', current: false },
    { name: 'Team Members', href: '#', current: false },
    { name: 'Billing', href: '#', current: false },
]

const dataStats = {
    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    datasets: [
        {
            label: 'Dataset 1',
            data: [34, 55, 47, 33, 42, 55, 37, 45, 43, 54, 49, 53],
            borderColor: layout.app.theme[0],
            backgroundColor: (context: ScriptableContext<"line">) => { const ctx = context.chart.ctx; const gradient = ctx.createLinearGradient(0, 0, 0, 200); gradient.addColorStop(0, layout.app.theme[0] + 'AA'); gradient.addColorStop(1, layout.app.theme[0] + '11'); return gradient; },
            tension: 0.5,
            fill: true
        },
        // {
        //     label: 'Dataset 1',
        //     data: [37, 49, 35, 43, 40, 52, 44, 49, 40, 52, 55, 47],
        //     borderColor: layout.app.theme[3],
        //     backgroundColor: layout.app.theme[2],
        //     tension: 0.5,
        // },
    ],
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

const selectedTabGraph = ref(0)
// console.log('liesaldnsa', Line)
</script>

<template>
    <Head :title="trans('Dashboard')" />
    <div class="px-4 py-6 grid grid-cols-3 gap-x-6">
        <div v-if="false" class="border border-gray-200 rounded-md shadow">
        </div>

        <div v-if="false" class="bg-slate-50 col-span-2 border border-gray-200 rounded-md shadow flex flex-col gap-y-8">
            <!-- <dl
                class="grid grid-cols-1 gap-x-8 gap-y-10 text-slate-600 sm:grid-cols-2 sm:gap-y-16 lg:mx-0 lg:max-w-none lg:grid-cols-4">
                <div v-for="stat in stats" :key="stat.id"
                    class="flex flex-col gap-y-3 border-l border-indigo-700/20 pl-6">
                    <dt class="text-sm leading-6">{{ stat.name }}</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight" :style="{ color: layout.app.theme[3] }">
                        {{ stat.value }}</dd>
                </div>
            </dl> -->
            <nav class="isolate flex divide-x divide-gray-200 rounded-br-lg shadow w-fit" aria-label="Tabs">
                <div v-for="(tab, tabIdx) in tabs" :key="tab.name"
                    @click="selectedTabGraph = tabIdx"
                    :class="[
                        selectedTabGraph == tabIdx? '' : 'text-gray-500 hover:text-gray-600',
                    ]"
                    class="last:rounded-br-lg group relative flex-1 py-2 px-4 text-center text-sm font-medium hover:bg-indigo-50 focus:z-10 cursor-pointer"
                >
                    <span class="whitespace-nowrap select-none">{{ tab.name }}</span>
                    <span aria-hidden="true"
                        :class="[selectedTabGraph == tabIdx ? 'bottomNavigationActive' : 'bottomNavigation', 'h-0.5']" />
                </div>
            </nav>

            <div class="px-4 py-3 ">
                <Line :data="dataStats" :options="config" />
            </div>
        </div>

        <!-- TODO: PKA-1550 -->
    </div>
</template>
