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
import { faCheckCircle, faInfoCircle, faExclamationTriangle } from '@fal'

import { library } from '@fortawesome/fontawesome-svg-core'
import CountUp from 'vue-countup-v3'
import index from "@/Components/Banners/SlidesWorkshop/Fields/index.vue";
library.add(faCheckCircle, faInfoCircle, faExclamationTriangle)

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

        
        <div  class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums col-span-2">
            <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ stats.pallets.label }}</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                    <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                        <!-- In Total -->
                        <div class="flex gap-x-2 items-end">
                            <CountUp :endVal="stats.pallets.count" :duration="1.5" :scrollSpyOnce="true" :options="{
                                formattingFn: (value: number) => locale.number(value)
                            }" />
                            <span class="text-sm font-medium leading-4 text-gray-500 ">{{ stats.pallets.description }}</span>
                        </div>

                        <div class="text-sm text-gray-500 flex gap-x-5 gap-y-1 items-center flex-wrap">


                              <div class="border border-red-500">
                                <FontAwesomeIcon :icon='stats.pallets.state.damaged.icon.icon' :class='stats.pallets.state.damaged.icon.class' fixed-width aria-hidden='true' />
                                <FontAwesomeIcon :icon='stats.pallets.state.lost.icon.icon' :class='stats.pallets.state.lost.icon.class' fixed-width aria-hidden='true' />
                                <FontAwesomeIcon :icon='stats.pallets.state.other_incident.icon.icon' :class='stats.pallets.state.other_incident.icon.class' fixed-width aria-hidden='true' />

                              </div>


<!--                            <div v-for="dCase in pie.cases" class="flex gap-x-0.5 items-center font-normal"-->
<!--                                v-tooltip="capitalize(dCase.icon.tooltip)">-->
<!--                                <FontAwesomeIcon :icon='dCase.icon.icon' :class='dCase.icon.class' fixed-width aria-hidden='true' />-->
<!--                                <span class="font-semibold">-->
<!--                                    {{ locale.number(dCase.count) }}-->
<!--                                </span>-->
<!--                            </div>-->
                        </div>
                    </div>
                </dd>
            </div>
        </div>

          <div class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums">
            <div class="">
              <dt class="text-base font-medium text-gray-400 capitalize">{{ stats.pallet_deliveries.label }}</dt>
              <dd class="mt-2 flex justify-between gap-x-2">
                <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                  <!-- In Total -->
                  <div class="flex gap-x-2 items-end">
                    <CountUp :endVal="stats.pallet_deliveries.count" :duration="1.5" :scrollSpyOnce="true" :options="{
                                formattingFn: (value: number) => locale.number(value)
                            }" />
                    <span class="text-sm font-medium leading-4 text-gray-500 ">{{ stats.pallet_deliveries.description }}</span>
                  </div>


                </div>
              </dd>
            </div>

          </div>


          <div  class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums">
              <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ stats.pallet_returns.label }}</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                  <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                    <!-- In Total -->
                    <div class="flex gap-x-2 items-end">
                      <CountUp :endVal="stats.pallet_returns.count" :duration="1.5" :scrollSpyOnce="true" :options="{
                                formattingFn: (value: number) => locale.number(value)
                            }" />
                      <span class="text-sm font-medium leading-4 text-gray-500 ">{{ stats.pallet_returns.description }}</span>
                    </div>


                  </div>
                </dd>
              </div>


        </div>

        
    </div>

</template>