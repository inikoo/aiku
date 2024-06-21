<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import CountUp from 'vue-countup-v3'
import { Pie } from 'vue-chartjs'
import { useLocaleStore } from "@/Stores/locale"

import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
ChartJS.register(ArcElement, Tooltip, Legend, Colors)

const props = defineProps<{
    data: {
        stored_item: {
            total_quantity: number
            slug: string
        }
        pieData: {
            stats: {
                label: string
                value: number
            }[]
        }
    }
    tab: any  // Not in used: to avoid warning  
    palletRoute: any  // Not in used: to avoid warning
    locationRoute: any  // Not in used: to avoid warning
    updateRoute: any  // Not in used: to avoid warning
}>()

const options = {
    responsive: true,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            // Popup: When the data set is hovered
            // enabled: false,
            titleFont: {
                size: 10,
                weight: 'lighter'
            },
            bodyFont: {
                size: 11,
                weight: 'bold'
            }
        },
    }
}

</script>

<template>
    <!-- <pre>{{ data }}</pre> -->
    <div class="px-8 py-6 grid grid-cols-2 gap-x-4">
        <!-- Box: Pie chart -->
        <div class="h-fit flex flex-col justify-between px-5 py-3 rounded-lg border border-gray-100 shadow tabular-nums">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="font-semibold leading-6">Pallet that contain this item <span class="font-light">({{ data.pieData.stats.length }})</span></h1>
                    <!-- <p class="text-sm font-light">
                        A list of pallets that contain item <span class="font-semibold">{{ 'Gelas' }}</span>
                    </p> -->
                </div>
                <!-- <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add
                        user</button>
                </div> -->
            </div>

            <!-- Pie -->
            <div class="w-full flex justify-center items-center">
                <div class="w-40 mx-auto">
                    <Pie :data="{
                        labels: Object.entries(data.pieData.stats).map(([, value]) => value.label),
                        datasets: [{
                            data: Object.entries(data.pieData.stats).map(([, value]) => value.value),
                            hoverOffset: 4
                        }]
                    }" :options="options" />
                </div>
            </div>
        </div>

        <!-- Mini Table -->
        <div class="flex flex-col gap-x-5 border border-gray-100 shadow rounded-md px-5 py-3 text-gray-500">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <!-- <h1 class="font-semibold leading-6">Pallets <span class="font-light">({{ data.pieData.stats.length }})</span></h1> -->
                    <p class="text-sm font-light">
                        A list of pallets that contain item <span class="font-semibold">{{ data.stored_item.slug }}</span>
                    </p>
                </div>
                <!-- <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add
                        user</button>
                </div> -->
            </div>
            
            <div class="mt-2 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold sm:pl-3">
                                        Pallet name
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold">
                                        Qty
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <tr v-for="(pallet, index) in data.pieData.stats" :key="pallet.label + index" class="even:bg-gray-50">
                                    <td class="whitespace-nowrap py-2.5 pl-4 pr-3 text-sm font-medium sm:pl-3">
                                        {{ pallet.label }}
                                    </td>
                                    <td class="tabular-nums text-right whitespace-nowrap pl-3 pr-6 py-2.5 text-sm text-gray-500">
                                        {{ useLocaleStore().number(pallet.value) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>