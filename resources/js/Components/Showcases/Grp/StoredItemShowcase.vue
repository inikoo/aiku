<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import CountUp from 'vue-countup-v3'
import { Pie } from 'vue-chartjs'
import { useLocaleStore } from "@/Stores/locale"
import tableStoredItemEdit from '@/Components/StoredItemMovement/TableStoredItemEdit.vue'

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
        route_pallets: routeType
    }
    
}>()

console.log(props)

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

console.log(props)

</script>

<template>
    <!-- <pre>{{ data }}</pre> -->
    <div class="px-8 py-6 grid grid-cols-6 gap-x-4">
        <!-- Box: Pie chart -->
        <div class="h-fit flex flex-col col-span-2 justify-between px-5 py-3 rounded-lg border border-gray-100 shadow tabular-nums">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="font-semibold leading-6">Pallet that contain this item <span class="font-light">({{ data.pieData.stats.length }})</span></h1>
                </div>
            </div>

            <!-- Pie -->
            <div class="w-full flex justify-center items-center">
                <div class="w-40 mx-auto my-5">
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
        <div class="flex flex-col col-span-4 gap-x-5 border border-gray-100 shadow rounded-md px-5 py-3 text-gray-500">
           <!--  <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <p class="text-sm font-light">
                        A list of pallets that contain item <span class="font-semibold">{{ data.stored_item.slug }}</span>
                    </p>
                </div>
            </div> -->
            
           <tableStoredItemEdit :data="data.pieData.stats" :route_pallets="data.route_pallets"/>
        </div>
    </div>
</template>