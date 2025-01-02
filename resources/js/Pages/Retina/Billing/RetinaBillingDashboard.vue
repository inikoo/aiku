<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 18 Feb 2024 06:25:34 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { trans } from "laravel-vue-i18n"
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
import { useLocaleStore } from "@/Stores/locale"
import { PalletCustomer } from '@/types/Pallet'
import CountUp from 'vue-countup-v3'
import DataView from 'primevue/dataview';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckCircle, faInfoCircle, faExclamationTriangle, faExclamationCircle, faDollarSign } from '@fal'
import { faSeedling, faShare, faSpellCheck, faCheck, faTimes, faSignOutAlt, faTruck, faCheckDouble, faCross } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useFormatTime } from '@/Composables/useFormatTime'

library.add(faCheckCircle, faInfoCircle, faExclamationTriangle, faSeedling, faShare, faSpellCheck, faCheck, faTimes, faSignOutAlt, faTruck, faCheckDouble, faCross)

ChartJS.register(ArcElement, Tooltip, Legend, Colors)

const props = defineProps<{
    title: string
    customer: PalletCustomer
    unpaid_invoices: Object
    transactionsData: {
        currency : {
            currency : {
                code : String
            }
        }
        transactions: {
            label: String,
            count: Number
            amount : Number
        },
        unpaid_bills: {
            label: String,
            count: Number
            amount : Number
        },
        paid_bills: {
            label: String,
            count: Number
            amount : Number
        },
    }
}>()

console.log('ss', props)
const locale = useLocaleStore()
const date = new Date()

</script>

<template>
    <div class="px-4 py-5 md:px-6 lg:px-8 space-y-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ trans("Storage Dashboard") }}</h1>
        <hr class="border-slate-300 rounded-full mb-5" />

        <!-- Card Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card: Total Transactions -->
            <div class="flex flex-col items-start justify-between p-6 rounded-lg shadow-md text-white"
                style="background: linear-gradient(to right, #4facfe, #00f2fe);">
                <div class="flex items-center gap-x-3">
                    <FontAwesomeIcon :icon="faCheckCircle" class="text-white text-lg" />
                    <dt class="text-lg font-medium capitalize">{{ transactionsData.transactions.label }}</dt>
                </div>
                <dd class="mt-4">
                    <div class="flex items-baseline gap-x-2">
                        <CountUp :endVal="transactionsData.transactions.count" :duration="1.5" :scrollSpyOnce="true"
                            :options="{ formattingFn: (value: number) => locale.number(value) }"
                            class="text-3xl font-bold" />
                        <span class="text-sm">{{ trans("Transactions") }}</span>
                    </div>
                </dd>
            </div>

            <!-- Card: Unpaid Bills -->
            <div class="flex flex-col items-start justify-between p-6 rounded-lg shadow-md text-white"
                style="background: linear-gradient(to right, #ff416c, #ff4b2b);">
                <div class="flex items-center gap-x-3">
                    <FontAwesomeIcon :icon="faExclamationCircle" class="text-yellow-200 text-lg" />
                    <dt class="text-lg font-medium capitalize">{{ transactionsData.unpaid_bills.label }}</dt>
                </div>
                <dd class="mt-4">
                    <div class="flex items-baseline gap-x-2">
                        <CountUp :endVal="transactionsData.unpaid_bills.count" :duration="1.5" :scrollSpyOnce="true"
                            :options="{ formattingFn: (value: number) => locale.number(value) }"
                            class="text-3xl font-bold" />
                        <span class="text-sm">{{ trans("Bills") }}</span>
                    </div>
                </dd>
            </div>

            <!-- Card: Paid Bills -->
            <div class="flex flex-col items-start justify-between p-6 rounded-lg shadow-md text-white"
                style="background: linear-gradient(to right, #56ab2f, #a8e063);">
                <div class="flex items-center gap-x-3">
                    <FontAwesomeIcon :icon="faDollarSign" class="text-green-300 text-lg" />
                    <dt class="text-lg font-medium capitalize">{{ transactionsData.paid_bills.label }}</dt>
                </div>
                <dd class="mt-4">
                    <div class="flex items-baseline gap-x-2">
                        <CountUp :endVal="transactionsData.paid_bills.count" :duration="1.5" :scrollSpyOnce="true"
                            :options="{ formattingFn: (value: number) => locale.number(value) }"
                            class="text-3xl font-bold" />
                        <span class="text-sm">{{ trans("Bills") }}</span>
                    </div>
                </dd>
            </div>
        </div>


        <hr class="border-slate-300 rounded-full mb-5" />

        <!-- Data List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Unpaid Bill List -->
            <div class="bg-gray-50 p-5 rounded-md shadow-md">
                <!-- Title -->
                <h2 class="text-lg font-semibold text-red-600 border-b pb-3 mb-4">{{ trans('Unpaid Bill List') }}</h2>
                <!-- DataView Component -->
                <DataView paginator :rows="unpaid_invoices.meta.per_page" :totalRecords="unpaid_invoices.meta.total"
                    :alwaysShowPaginator="false" :value="unpaid_invoices.data" :pageLinkSize="3"
                    @page="(a, s, d) => console.log(a, s, d)">
                    <template #list="slotProps">
                        <div class="space-y-4 bg-gray-50 mb-2">
                            <div v-for="(item, index) in slotProps.items" :key="index"
                                class="flex flex-col py-2 px-4 border rounded-lg shadow hover:bg-gray-100 bg-white transition">
                                <!-- Header -->
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-4">
                                        <FontAwesomeIcon icon="truck" class="text-blue-500 text-2xl" />
                                        <div>
                                            <span class="text-sm text-gray-500">{{ useFormatTime(item.date) }}</span>
                                            <p class="text-lg font-semibold text-gray-900">{{ item.reference }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xl font-bold text-red-600">{{
                                        useLocaleStore().currencyFormat(item.currency_code, item.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </DataView>
            </div>

            <!-- Summary Card -->
            <div class="bg-gray-50 p-5 rounded-md shadow-md">
                <h2 class="text-lg font-semibold text-blue-600 border-b pb-3 mb-4">{{ trans('Summary') }}</h2>
                <div class="space-y-4">
                      <!-- Total Transactions -->
                      <div class="flex flex-col bg-white p-6 border rounded-lg shadow">
                        <div class="flex justify-between items-center border-b pb-3 mb-3">
                            <span class="text-gray-500 text-sm">{{ trans('Total Transactions') }}</span>
                            <span class="text-xl font-bold text-green-600">{{ useLocaleStore().currencyFormat(transactionsData.currency.currency.code, transactionsData.transactions.amount) }}</span>
                        </div>
                        <div class="flex flex-col space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500 text-sm">{{ trans('Completed Transactions') }}</span>
                                <span class="text-sm text-gray-700">{{transactionsData.paid_bills.count}}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Total Bill -->
                    <div class="flex flex-col bg-white p-6 border rounded-lg shadow">
                        <div class="flex justify-between items-center border-b pb-3 mb-3">
                            <span class="text-gray-500 text-sm">{{ trans('Total Bills') }}</span>
                            <span class="text-xl font-bold text-green-600">{{ useLocaleStore().currencyFormat(transactionsData.currency.currency.code, transactionsData.unpaid_bills.amount) }}</span>
                        </div>
                        <div class="flex flex-col space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500 text-sm">{{ trans('Status') }}</span>
                                <span class="text-sm font-semibold text-red-600">Unpaid</span>
                            </div>
                        </div>
                    </div>
                  
                </div>
            </div>
        </div>


    </div>
</template>

<style scoped></style>