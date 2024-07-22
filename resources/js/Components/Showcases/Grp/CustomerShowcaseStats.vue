<script setup lang='ts'>
import { Pie } from 'vue-chartjs'
import { trans } from "laravel-vue-i18n"
import { capitalize } from '@/Composables/capitalize'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
import { useLocaleStore } from "@/Stores/locale"
import { CustomerPalletData, PieCustomer } from "@/types/Pallet";

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

const props = defineProps<{
  dashboardData: {
        [key: string]: CustomerPalletData
    }

}>()



</script>

<template>
    <div class="grid grid-cols-2 gap-y-2.5 gap-x-3 text-gray-600">

        
        <div  class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums col-span-2">
            <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ dashboardData.pallets.label }}</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                    <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                        <!-- In Total -->
                        <div class="flex gap-x-2 items-end">
                            <CountUp :endVal="dashboardData.pallets.count" :duration="1.5" :scrollSpyOnce="true" :options="{
                                formattingFn: (value: number) => locale.number(value)
                            }" />
                            <span class="text-sm font-medium leading-4 text-gray-500 ">{{ dashboardData.pallets.description }}</span>
                        </div>

                        <div class="text-sm text-gray-500 flex gap-x-5 gap-y-1 items-center flex-wrap">


                              <div class="border border-red-500">
                                <FontAwesomeIcon :icon='dashboardData.pallets.state.damaged.icon.icon' :class='dashboardData.pallets.state.damaged.icon.class' fixed-width aria-hidden='true' />
                                <FontAwesomeIcon :icon='dashboardData.pallets.state.lost.icon.icon' :class='dashboardData.pallets.state.lost.icon.class' fixed-width aria-hidden='true' />
                                <FontAwesomeIcon :icon='dashboardData.pallets.state.other_incident.icon.icon' :class='dashboardData.pallets.state.other_incident.icon.class' fixed-width aria-hidden='true' />

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
              <dt class="text-base font-medium text-gray-400 capitalize">{{ dashboardData.pallet_deliveries.label }}</dt>
              <dd class="mt-2 flex justify-between gap-x-2">
                <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                  <!-- In Total -->
                  <div class="flex gap-x-2 items-end">
                    <CountUp :endVal="dashboardData.pallet_deliveries.count" :duration="1.5" :scrollSpyOnce="true" :options="{
                                formattingFn: (value: number) => locale.number(value)
                            }" />
                    <span class="text-sm font-medium leading-4 text-gray-500 ">{{ dashboardData.pallet_deliveries.description }}</span>
                  </div>


                </div>
              </dd>
            </div>

          </div>


          <div  class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-100 shadow tabular-nums">
              <div class="">
                <dt class="text-base font-medium text-gray-400 capitalize">{{ dashboardData.pallet_returns.label }}</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                  <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                    <!-- In Total -->
                    <div class="flex gap-x-2 items-end">
                      <CountUp :endVal="dashboardData.pallet_returns.count" :duration="1.5" :scrollSpyOnce="true" :options="{
                                formattingFn: (value: number) => locale.number(value)
                            }" />
                      <span class="text-sm font-medium leading-4 text-gray-500 ">{{ dashboardData.pallet_returns.description }}</span>
                    </div>


                  </div>
                </dd>
              </div>


        </div>

        
    </div>

</template>