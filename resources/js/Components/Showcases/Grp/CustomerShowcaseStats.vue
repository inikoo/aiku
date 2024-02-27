<script setup lang='ts'>
import { Pie } from 'vue-chartjs'
import { trans } from "laravel-vue-i18n"
import { capitalize } from '@/Composables/capitalize'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
import { useLocaleStore } from "@/Stores/locale"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckCircle, faInfoCircle, faExclamationTriangle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCheckCircle, faInfoCircle, faExclamationTriangle)

ChartJS.register(ArcElement, Tooltip, Legend, Colors)
const locale = useLocaleStore()

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

const dummyPieValue = [
    {
        "label": "Item 1",
        "count": 35,
        "cases": [
            { "value": "Case 1", "count": 83, "label": "Case 1 Label", "icon": { "name": "fal fa-check-circle", "tooltip": "Tooltip for Case 1", "class": "case-1" } },
            { "value": "Case 2", "count": 12, "label": "Case 2 Label", "icon": { "name": "fal fa-info-circle", "tooltip": "Tooltip for Case 2", "class": "case-2" } }
        ]
    },
    {
        "label": "Item 2",
        "count": 91,
        "cases": [
            { "value": "Case 1", "count": 41, "label": "Case 1 Label", "icon": { "name": "fal fa-exclamation-triangle", "tooltip": "Tooltip for Case 1", "class": "case-1" } },
            { "value": "Case 2", "count": 75, "label": "Case 2 Label", "icon": { "name": "fal fa-info-circle", "tooltip": "Tooltip for Case 2", "class": "case-2" } },
            { "value": "Case 3", "count": 18, "label": "Case 3 Label", "icon": { "name": "fal fa-check-circle", "tooltip": "Tooltip for Case 3", "class": "case-3" } }
        ]
    },
    {
        "label": "Item 3",
        "count": 23,
        "cases": [
            { "value": "Case 1", "count": 13, "label": "Case 1 Label", "icon": { "name": "fal fa-check-circle", "tooltip": "Tooltip for Case 1", "class": "case-1" } },
            { "value": "Case 2", "count": 10, "label": "Case 2 Label", "icon": { "name": "fal fa-info-circle", "tooltip": "Tooltip for Case 2", "class": "case-2" } }
        ]
    },
    {
        "label": "Item 4",
        "count": 57,
        "cases": [
            { "value": "Case 1", "count": 37, "label": "Case 1 Label", "icon": { "name": "fal fa-exclamation-triangle", "tooltip": "Tooltip for Case 1", "class": "case-1" } },
            { "value": "Case 2", "count": 15, "label": "Case 2 Label", "icon": { "name": "fal fa-info-circle", "tooltip": "Tooltip for Case 2", "class": "case-2" } },
            { "value": "Case 3", "count": 5, "label": "Case 3 Label", "icon": { "name": "fal fa-check-circle", "tooltip": "Tooltip for Case 3", "class": "case-3" } }
        ]
    },
]
</script>

<template>
    <div class="grid grid-cols-2 gap-y-2 gap-x-2 text-gray-600">
        <div v-for="prospectState in dummyPieValue" class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums">
            <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ prospectState.label }}</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                    <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                        <!-- In Total -->
                        <div class="flex gap-x-2 items-end">
                            {{ locale.number(prospectState.count) }}
                            <span class="text-sm font-medium leading-4 text-gray-500 ">{{ trans('in total') }}</span>
                        </div>
                        <!-- Statistic -->
                        <div class="text-sm text-gray-500 flex gap-x-5 gap-y-1 items-center flex-wrap">
                            <div v-for="dCase in prospectState.cases" class="flex gap-x-0.5 items-center font-normal"
                                v-tooltip="capitalize(dCase.icon.tooltip)">
                                <FontAwesomeIcon :icon='dCase.icon.name' :class='dCase.icon.class' fixed-width aria-hidden='true' />
                                <span class="font-semibold">
                                    {{ locale.number(dCase.count) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </dd>
            </div>
            <!-- Donut -->
            <div class="w-20">
                <Pie :data="{
                    labels: Object.entries(prospectState.cases).map(([, value]) => value.label),
                    datasets: [{
                        data: Object.entries(prospectState.cases).map(([, value]) => value.count),
                        hoverOffset: 4
                    }]
                }" :options="options" />
            </div>
        </div>
    </div>
</template>