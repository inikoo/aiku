<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 18 Feb 2024 06:25:34 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Pie } from 'vue-chartjs'
import { trans } from "laravel-vue-i18n"
import { capitalize } from '@/Composables/capitalize'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
import { useLocaleStore } from "@/Stores/locale"
import { PalletCustomer, FulfilmentCustomerStats } from '@/types/Pallet'
import { ref, onMounted } from "vue";

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckCircle, faInfoCircle, faExclamationTriangle } from '@fal'
import { faSeedling, faShare, faSpellCheck, faCheck, faTimes, faSignOutAlt, faTruck, faCheckDouble, faCross } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useFormatTime } from '@/Composables/useFormatTime'
import CountUp from 'vue-countup-v3'
import { Head } from '@inertiajs/vue3'
import DataView from 'primevue/dataview';

import '@/Composables/Icon/PalletStateEnum'

library.add(faCheckCircle, faInfoCircle, faExclamationTriangle, faSeedling, faShare, faSpellCheck, faCheck, faTimes, faSignOutAlt, faTruck, faCheckDouble, faCross)

ChartJS.register(ArcElement, Tooltip, Legend, Colors)

const props = defineProps<{
    title: string
    customer: PalletCustomer
    storageData: {
        [key: string]: FulfilmentCustomerStats
    }
}>()

const list = [
    {
        reference: 'as23445',
        date: new Date(),
        total: 903
    },
    {
        reference: 'as234we5',
        date: new Date(),
        total: 903
    },
    {
        reference: 'as234f',
        date: new Date(),
        total: 903
    },
    {
        reference: 'as234d',
        date: new Date(),
        total: 903
    }
]

const locale = useLocaleStore()

</script>

<template>
    <div class="px-4 py-5 md:px-6 lg:px-8 space-y-6">
      <h1 class="text-2xl font-bold text-gray-800">Storage Dashboard</h1>
      <hr class="border-slate-300 rounded-full mb-5" />
  
      <!-- Card Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Transactions -->
        <div class="flex flex-col items-start justify-between p-6 rounded-lg shadow-md text-white" style="background: linear-gradient(to right, #4facfe, #00f2fe);">
          <div class="flex items-center gap-x-3">
            <FontAwesomeIcon icon="check-circle" class="text-white text-lg" />
            <dt class="text-lg font-medium capitalize">Total Transactions</dt>
          </div>
          <dd class="mt-4">
            <div class="flex items-baseline gap-x-2">
              <CountUp :endVal="323" :duration="1.5" :scrollSpyOnce="true" :options="{ formattingFn: (value: number) => locale.number(value) }" class="text-3xl font-bold" />
              <span class="text-sm">Transactions</span>
            </div>
          </dd>
        </div>
  
        <!-- Unpaid Bills -->
        <div class="flex flex-col items-start justify-between p-6 rounded-lg shadow-md text-white" style="background: linear-gradient(to right, #ff416c, #ff4b2b);">
          <div class="flex items-center gap-x-3">
            <FontAwesomeIcon icon="exclamation-triangle" class="text-white text-lg" />
            <dt class="text-lg font-medium capitalize">Unpaid Bills</dt>
          </div>
          <dd class="mt-4">
            <div class="flex items-baseline gap-x-2">
              <CountUp :endVal="34" :duration="1.5" :scrollSpyOnce="true" :options="{ formattingFn: (value: number) => locale.number(value) }" class="text-3xl font-bold" />
              <span class="text-sm">Bills</span>
            </div>
          </dd>
        </div>
  
        <!-- Paid Bills -->
        <div class="flex flex-col items-start justify-between p-6 rounded-lg shadow-md text-white" style="background: linear-gradient(to right, #56ab2f, #a8e063);">
          <div class="flex items-center gap-x-3">
            <FontAwesomeIcon icon="check-double" class="text-white text-lg" />
            <dt class="text-lg font-medium capitalize">Paid Bills</dt>
          </div>
          <dd class="mt-4">
            <div class="flex items-baseline gap-x-2">
              <CountUp :endVal="565" :duration="1.5" :scrollSpyOnce="true" :options="{ formattingFn: (value: number) => locale.number(value) }" class="text-3xl font-bold" />
              <span class="text-sm">Bills</span>
            </div>
          </dd>
        </div>
      </div>
  
      <!-- Data List Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <!-- Unpaid Bill List -->
  <div class="bg-gray-50 p-5 rounded-md shadow-md">
    <!-- Title -->
    <h2 class="text-lg font-semibold text-gray-700 border-b pb-3 mb-4">Unpaid Bill List</h2>
    <!-- DataView Component -->
    <DataView :value="list">
      <template #list="slotProps">
        <div class="space-y-4 bg-gray-50">
          <div
            v-for="(item, index) in slotProps.items"
            :key="index"
            class="flex flex-col p-4 border rounded-lg shadow hover:bg-gray-100 transition"
          >
            <!-- Header -->
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-4">
                <FontAwesomeIcon icon="truck" class="text-blue-500 text-2xl" />
                <div>
                  <span class="text-sm text-gray-500">{{ useFormatTime(item.date) }}</span>
                  <p class="text-lg font-semibold text-gray-900">{{ item.reference }}</p>
                </div>
              </div>
              <span class="text-xl font-bold text-red-600">${{ item.total }}</span>
            </div>
          </div>
        </div>
      </template>
    </DataView>
  </div>
</div>

    </div>
  </template>
  
<style scoped>
</style>