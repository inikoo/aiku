<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import CountUp from 'vue-countup-v3'
import { Pie } from 'vue-chartjs'
import { useLocaleStore } from "@/Stores/locale"
import TableStoredItemEdit from '@/Components/StoredItemMovement/TableStoredItemEdit.vue'
import { ref } from 'vue'
import { Link, router } from "@inertiajs/vue3"

import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
ChartJS.register(ArcElement, Tooltip, Legend, Colors)

const props = defineProps<{
    data: {
        stored_item: {
            total_quantity: number,
            slug: string,
        },
        pieData: {
            stats: {
                label: string,
                value: number,
            }[]
        },
        route_pallets: routeType,
        pallets: Array<{ label: string, location: string, value: number }>
        route_update_stored_item : routeType
    }
}>()
const isLoading = ref(false)
const options = {
    responsive: true,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
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

const onChangeStoredItem = (data) => {
    router.patch(
        route(props.data.route_update_stored_item.name,props.data.route_update_stored_item.parameters),
        {pallets : data},
        {
            onBefore: () => {
                isLoading.value = true
            },
            onSuccess: () => {
                null
            },
            onError: (error: {} | string) => {
                isLoading.value = false
                notify({
                    title: 'Something went wrong.',
                    text: 'failed to save',
                    type: 'error',
                })
            }
        })
}

</script>

<template>
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
                        labels: data.pieData.stats.map(stat => stat.label),
                        datasets: [{
                            data: data.pieData.stats.map(stat => stat.value),
                            hoverOffset: 4
                        }]
                    }" :options="options" />
                </div>
            </div>
        </div>

        <!-- Mini Table -->
        <div class="flex flex-col col-span-4 gap-x-5 border border-gray-100 shadow rounded-md px-5 py-3 text-gray-500">
            <TableStoredItemEdit 
                :data="data.pallets" 
                :route_pallets="data.route_pallets" 
                @Save="onChangeStoredItem" 
                :loading="isLoading"/>
        </div>
    </div>
</template>
