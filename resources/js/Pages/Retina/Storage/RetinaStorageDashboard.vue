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

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckCircle, faInfoCircle, faExclamationTriangle } from '@fal'
import { faSeedling, faShare, faSpellCheck, faCheck, faTimes, faSignOutAlt, faTruck, faCheckDouble, faCross } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useFormatTime } from '@/Composables/useFormatTime'
import CountUp from 'vue-countup-v3'
import { Head } from '@inertiajs/vue3'

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

const locale = useLocaleStore()

const options = {
    responsive: true,
    plugins: {
        emptyPie: {
            color: 'rgba(255, 128, 0, 0.5)',
            width: 2,
            radiusDecrease: 20
        },
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
    <Head :title="title" />
    <div class="px-4 py-5 md:px-6 lg:px-8 ">
        <h1 class="text-2xl font-bold">Storage Dashboard</h1>
        <hr class="border-slate-200 rounded-full mb-5 mt-2">

        <div class="grid md:grid-cols-2 gap-y-4 md:gap-y-0 gap-x-3">
            <!-- Section: Profile box -->
            <div class="h-fit bg-slate-50 border border-slate-200 text-retina-600 p-6 flex flex-col justify-between rounded-lg shadow overflow-hidden">
                <div class="w-full">
                    <h2 v-if="customer?.name" class="text-3xl font-bold">{{ customer?.name }}</h2>
                    <h2 v-else class="text-3xl font-light italic brightness-75">{{ trans('No name') }}</h2>
                    <div class="text-lg">
                        {{ customer?.shop }}
                        <!-- <span class="text-gray-400">
                            ({{ customer?.number_current_clients || 0 }} clients)
                        </span> -->
                    </div>
                </div>
                
                <div class="mt-4 space-y-3 text-sm text-slate-500">
                    <div class="border-l-2 border-slate-500 pl-4">
                        <h3 class="font-light">Phone</h3>
                        <address class="text-base font-medium not-italic text-gray-600">
                            <p>{{ customer?.phone || '-' }}</p>
                        </address>
                    </div>
                    <div class="border-l-2 border-slate-500 pl-4">
                        <h3 class="font-light">Email</h3>
                        <address class="text-base font-medium not-italic text-gray-600">
                            <p>{{ customer?.email || '-' }}</p>
                        </address>
                    </div>
                    <div class="border-l-2 border-slate-500 pl-4">
                        <h3 class="font-light">Member since</h3>
                        <address class="text-base font-medium not-italic text-gray-600">
                            <p>{{ useFormatTime(customer?.created_at) || '-' }}</p>
                        </address>
                    </div>
                </div>
            </div>

            <!-- Section: Stats box -->
            <div class="grid md:grid-cols-2 gap-y-3 gap-x-2 text-gray-600">

                <div
                    class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-300 tabular-nums col-span-2">
                    <div class="">
                        <dt class="text-base font-medium text-gray-400 capitalize">{{ storageData.pallets.label }}</dt>
                        <dd class="mt-2 flex justify-between gap-x-2">
                            <div
                                class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                                <!-- In Total -->
                                <div class="flex gap-x-2 items-end">
                                    <CountUp :endVal="storageData.pallets.count" :duration="1.5" :scrollSpyOnce="true"
                                        :options="{
                                            formattingFn: (value: number) => locale.number(value)
                                        }" />
                                    <span class="text-sm font-medium leading-4 text-gray-400 ">
                                        {{ storageData.pallets.description }}
                                    </span>
                                </div>

                                <div v-if="storageData.pallets.state?.damaged?.count || storageData.pallets.state?.lost?.count || storageData.pallets.state?.['other-incident'].count" class="">
                                    <div class="text-sm text-red-400 border border-red-300 bg-red-50 rounded px-2 py-2 font-normal">
                                        <div v-if="!storageData.pallets.state?.damaged?.count">
                                            <FontAwesomeIcon :icon='storageData.pallets.state?.damaged?.icon.icon' :class='storageData.pallets.state?.damaged?.icon.class' fixed-width aria-hidden='true' />
                                            <!-- Damaged: -->
                                            {{ storageData.pallets.state?.damaged?.count }}
                                        </div>
                                        <div v-if="!storageData.pallets.state?.lost?.count">
                                            <FontAwesomeIcon :icon='storageData.pallets.state?.lost?.icon.icon' :class='storageData.pallets.state?.lost?.icon.class' fixed-width aria-hidden='true' />
                                            <!-- Lost: -->
                                            {{ storageData.pallets.state?.lost?.count }}
                                        </div>
                                        <div v-if="!storageData.pallets.state?.['other-incident'].count">
                                            <FontAwesomeIcon :icon="storageData.pallets.state?.['other-incident']?.icon?.icon" :class="storageData.pallets.state?.['other-incident']?.icon?.class" fixed-width aria-hidden='true' />
                                            <!-- Other incident: -->
                                            {{ storageData.pallets.state?.['other-incident'].count }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </dd>
                    </div>
                </div>

                <div class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-300 tabular-nums">
                    <div class="">
                        <dt class="text-base font-medium text-gray-400 capitalize">{{ storageData.pallet_deliveries.label }}
                        </dt>
                        <dd class="mt-2 flex justify-between gap-x-2">
                            <div
                                class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                                <!-- In Total -->
                                <div class="flex gap-x-2 items-end">
                                    <CountUp :endVal="storageData.pallet_deliveries.count" :duration="1.5" :scrollSpyOnce="true"
                                        :options="{
                                            formattingFn: (value: number) => locale.number(value)
                                        }" />
                                    <span class="text-sm font-medium leading-4 text-gray-500 ">
                                        {{ storageData.pallet_deliveries.description }}
                                    </span>
                                </div>
                            </div>
                        </dd>
                    </div>
                </div>


                <div class="flex  justify-between px-4 py-5 sm:p-6 rounded-lg bg-white border border-gray-300 tabular-nums">
                    <div class="">
                        <dt class="text-base font-medium text-gray-400 capitalize">{{ storageData.pallet_returns.label }}</dt>
                        <dd class="mt-2 flex justify-between gap-x-2">
                            <div
                                class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                                <!-- In Total -->
                                <div class="flex gap-x-2 items-end">
                                    <CountUp :endVal="storageData.pallet_returns.count" :duration="1.5" :scrollSpyOnce="true"
                                        :options="{
                                            formattingFn: (value: number) => locale.number(value)
                                        }" />
                                    <span class="text-sm font-medium leading-4 text-gray-500 ">{{
                                        storageData.pallet_returns.description }}</span>
                                </div>
                            </div>
                        </dd>
                    </div>
                </div>

            </div>
        </div>
        <!-- <pre>{{ props }}</pre> -->
    </div>
</template>
