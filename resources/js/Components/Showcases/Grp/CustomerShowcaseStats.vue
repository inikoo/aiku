<script setup lang='ts'>
import { Pie } from 'vue-chartjs'
import { trans } from "laravel-vue-i18n"
import { capitalize } from '@/Composables/capitalize'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
import { useLocaleStore } from "@/Stores/locale"
import { FulfilmentCustomerStats, PieCustomer } from "@/types/Pallet";

import '@/Composables/Icon/PalletStateEnum.ts'
import '@/Composables/Icon/PalletDeliveryStateEnum.ts'
import '@/Composables/Icon/PalletReturnStateEnum.ts'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckCircle, faInfoCircle, faExclamationTriangle, faCheck } from '@fal'

import { library } from '@fortawesome/fontawesome-svg-core'
import CountUp from 'vue-countup-v3'
import index from "@/Components/Banners/SlidesWorkshop/Fields/index.vue"
library.add(faCheckCircle, faInfoCircle, faExclamationTriangle, faCheck)

ChartJS.register(ArcElement, Tooltip, Legend, Colors)
const locale = useLocaleStore()

defineProps<{
  stats: {
        [key: string]: FulfilmentCustomerStats
    }

}>()



</script>

<template>
    <div class="grid grid-cols-2 gap-y-2.5 gap-x-3 text-gray-600">


        <div
            class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums col-span-2">
            <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ stats.pallets.label }}</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                    <div
                        class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                        <!-- In Total -->
                        <div class="flex gap-x-2 items-end">
                            <CountUp :endVal="stats.pallets.count" :duration="1.5" :scrollSpyOnce="true"
                                :options="{
                                    formattingFn: (value: number) => locale.number(value)
                                }" />
                            <span class="text-sm font-medium leading-4 text-gray-400 ">
                                {{ stats.pallets.description }}
                            </span>
                        </div>

                        <div class="">
                            <div v-if="stats.pallets.state.damaged.count || stats.pallets.state.lost.count || stats.pallets.state['other-incident'].count" class="text-sm text-red-400 border border-red-300 bg-red-50 rounded px-2 py-2 font-normal">
                                <div v-if="!stats.pallets.state.damaged.count">
                                    <FontAwesomeIcon :icon='stats.pallets.state.damaged.icon.icon' :class='stats.pallets.state.damaged.icon.class' fixed-width aria-hidden='true' />
                                    Damaged:
                                    {{ stats.pallets.state.damaged.count }}
                                </div>
                                <div v-if="!stats.pallets.state.lost.count">
                                    <FontAwesomeIcon :icon='stats.pallets.state.lost.icon.icon' :class='stats.pallets.state.lost.icon.class' fixed-width aria-hidden='true' />
                                    Lost:
                                    {{ stats.pallets.state.lost.count }}
                                </div>
                                <div v-if="!stats.pallets.state['other-incident'].count">
                                    <FontAwesomeIcon :icon='stats.pallets.state["other-incident"].icon.icon' :class='stats.pallets.state["other-incident"].icon.class' fixed-width aria-hidden='true' />
                                    Other incident:
                                    {{ stats.pallets.state['other-incident'].count }}
                                </div>
                            </div>

                            <div v-else class="font-normal text-sm text-green-400">
                                <FontAwesomeIcon icon='fal fa-check' class='' fixed-width aria-hidden='true' />
                                All pallets are fine.
                            </div>
                        </div>
                    </div>
                </dd>
            </div>
        </div>

        <div
            class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums">
            <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ stats.pallet_deliveries.label }}
                </dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                    <div
                        class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                        <!-- In Total -->
                        <div class="flex gap-x-2 items-end">
                            <CountUp :endVal="stats.pallet_deliveries.count" :duration="1.5"
                                :scrollSpyOnce="true" :options="{
                                    formattingFn: (value: number) => locale.number(value)
                                }" />
                            <span class="text-sm font-medium leading-4 text-gray-500 ">
                                {{ stats.pallet_deliveries.description }}
                            </span>
                        </div>


                    </div>
                </dd>
            </div>

        </div>


        <div
            class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums">
            <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ stats.pallet_returns.label }}</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                    <div
                        class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                        <!-- In Total -->
                        <div class="flex gap-x-2 items-end">
                            <CountUp :endVal="stats.pallet_returns.count" :duration="1.5" :scrollSpyOnce="true"
                                :options="{
                                    formattingFn: (value: number) => locale.number(value)
                                }" />
                            <span class="text-sm font-medium leading-4 text-gray-500 ">{{
                                stats.pallet_returns.description }}</span>
                        </div>


                    </div>
                </dd>
            </div>


        </div>


    </div>

</template>