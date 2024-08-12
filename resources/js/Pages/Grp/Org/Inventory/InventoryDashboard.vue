<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 14 Sept 2022 15:29:08 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import FlatTreeMap from "@/Components/Navigation/FlatTreeMap.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Pie } from "vue-chartjs"
import { trans } from "laravel-vue-i18n"
import { faSeedling, faThumbsDown, faPalletAlt } from "@fal"
import { faCheckCircle, faTimesCircle, faPauseCircle } from "@fas"

import { capitalize } from "@/Composables/capitalize"
import { useLocaleStore } from "@/Stores/locale"

import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from "chart.js"
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"

library.add(faSeedling, faThumbsDown, faTimesCircle, faPauseCircle, faCheckCircle, faPalletAlt)

ChartJS.register(ArcElement, Tooltip, Legend, Colors)
const locale = useLocaleStore()

defineProps<{
    title: string
    pageHead: PageHeadingTypes
    flatTreeMaps: {}
    dashboardStats: {
        [key: string]: {
            label: string
            count: number
            cases: {
                value: string
                count: number
                label: string
                icon: {
                    icon: string | string[]
                    tooltip: string
                    class: string
                }
            }[]
        }
    }
}>()


// Pie: options
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
                weight: "lighter"
            },
            bodyFont: {
                size: 11,
                weight: "bold"
            }
        }
    }
}
</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <FlatTreeMap class="mx-4" v-for="(treeMap, idx) in flatTreeMaps" :key="idx" :nodes="treeMap" />

    <dl class="px-4 mt-5 grid grid-cols-1 md:grid-cols-2 gap-x-2 gap-y-3">
        <div v-for="stats in dashboardStats"
            class="px-4 py-5 sm:p-6 rounded-lg bg-white shadow tabular-nums ring-3 ring-gray-300">
            <dt class="text-base font-medium text-gray-400 capitalize">{{ stats.label }}</dt>
            <dd class="mt-2 flex justify-between gap-x-2">
                <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold">
                    <!-- In Total -->
                    <div class="flex gap-x-2 items-end">
                        {{ locale.number(stats.count) }}
                        <span class="text-sm font-medium leading-4 text-gray-500 ">{{ trans("current") }}</span>
                    </div>

                    <!-- Statistic -->
                    <div class="text-sm text-gray-500 flex gap-x-5 gap-y-1 items-center flex-wrap">
                        <div v-for="sCase in stats.cases" class="flex gap-x-0.5 items-center font-normal"
                            v-tooltip="capitalize(sCase.icon.tooltip)">
                            <FontAwesomeIcon :icon="sCase.icon.icon" :class="sCase.icon.class" fixed-width
                                :title="sCase.icon.tooltip" aria-hidden="true" />
                            <span class="font-semibold">
                                {{ locale.number(sCase.count) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Donut -->
                <div class="w-20">
                    <Pie :data="{
                        labels: Object.entries(stats.cases).map(([, value]) => value.label),
                        datasets: [{
                            data: Object.entries(stats.cases).map(([, value]) => value.count),
                            hoverOffset: 4
                        }]
                    }" :options="options" />
                </div>
            </dd>
        </div>
    </dl>
</template>
