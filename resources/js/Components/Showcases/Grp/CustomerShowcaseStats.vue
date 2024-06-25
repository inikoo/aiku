<script setup lang='ts'>
import { Pie } from 'vue-chartjs'
import { trans } from "laravel-vue-i18n"
import { capitalize } from '@/Composables/capitalize'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
import { useLocaleStore } from "@/Stores/locale"
import { PieCustomer } from '@/types/Pallet'

import '@/Composables/Icon/PalletStateEnum.ts'
import '@/Composables/Icon/PalletDeliveryStateEnum.ts'
import '@/Composables/Icon/PalletReturnStateEnum.ts'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckCircle, faInfoCircle, faExclamationTriangle } from '@fal'

import { library } from '@fortawesome/fontawesome-svg-core'
import CountUp from 'vue-countup-v3'
library.add(faCheckCircle, faInfoCircle, faExclamationTriangle)

ChartJS.register(ArcElement, Tooltip, Legend, Colors)
const locale = useLocaleStore()

const props = defineProps<{
    pieData: {
        [key: string]: PieCustomer
    }
    warehouseSummary: {
        [key: string]: number
    }
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
    <div class="grid grid-cols-2 gap-y-2.5 gap-x-3 text-gray-600">
        <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-lg border border-gray-100 shadow ">
            <div class="font-medium text-lg mb-4">
                Warehouse summary
            </div>

            <div class="flex flex-col">
                <div>Pallets stored: {{ warehouseSummary?.pallets_stored || 0 }}</div>
                <div>Total items: {{ warehouseSummary?.total_items || 0 }} ({{ warehouseSummary?.unique_items || 0 }} unique items) </div>
            </div>
        </div>
        
        <div v-for="(pie, index) in pieData" class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums"
            :class="[index === 'palletsxxx' ? 'col-span-2' : '']"
        >
            <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ pie.label }}</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                    <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                        <!-- In Total -->
                        <div class="flex gap-x-2 items-end">
                            <CountUp :endVal="pie.count" :duration="1.5" :scrollSpyOnce="true" :options="{
                                formattingFn: (value: number) => locale.number(value)
                            }" />
                            <span class="text-sm font-medium leading-4 text-gray-500 ">{{ trans('in total') }}</span>
                        </div>
                        <!-- Statistic -->
                        <div class="text-sm text-gray-500 flex gap-x-5 gap-y-1 items-center flex-wrap">
                            <div v-for="dCase in pie.cases" class="flex gap-x-0.5 items-center font-normal"
                                v-tooltip="capitalize(dCase.icon.tooltip)">
                                <FontAwesomeIcon :icon='dCase.icon.icon' :class='dCase.icon.class' fixed-width aria-hidden='true' />
                                <span class="font-semibold">
                                    {{ locale.number(dCase.count) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </dd>
            </div>

            <!-- Pie -->
            <div class="w-20">
                <Pie :data="{
                    labels: Object.entries(pie.cases).map(([, value]) => value.label),
                    datasets: [{
                        data: Object.entries(pie.cases).map(([, value]) => value.count),
                        hoverOffset: 4
                    }]
                }" :options="options" />
            </div>
        </div>

        
    </div>
</template>