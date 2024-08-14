<script setup lang="ts">
import { inject, ref } from 'vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { Switch } from '@headlessui/vue'
import { useLocaleStore } from '@/Stores/locale'
import { RadioGroup, RadioGroupOption } from '@headlessui/vue'
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronDown } from '@far'
import { faTriangle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Head } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { get } from 'lodash'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { useGetCurrencySymbol } from '@/Composables/useCurrency'
import Tag from '@/Components/Tag.vue'

library.add(faTriangle, faChevronDown)

const props = defineProps<{
    groupStats: {
        currency: {
            code: string
        }
        organisations: {
            name: string
            type: string
            code: string
            currency: {
                code: string
            }
            invoices: {
                number_invoices: number
            }
            sales: {
                number_sales: number
            }
            refunds: {
                number_refunds: number
            }

            // number_invoices_type_refund?: number
            // number_invoices?: number
            // number_invoices_type_invoice?: number
            interval_percentages?: {
                sales?: {
                    [key: string]: {
                        percentage: number
                        difference: number
                    }
                }
                invoices?: {
                    [key: string]: {
                        percentage: number
                        difference: number
                    }
                }
                refunds?: {
                    [key: string]: {
                        percentage: number
                        difference: number
                    }
                }
            }
        }[]
    }
    interval_options: {
        label: string
        labelShort: string
        value: string
    }[]
}>()

// console.log('asdsadsa', props.groupStats.organisations)
const layout = inject('layout', layoutStructure)
const locale = inject('locale', {})


// Decriptor: Date interval
const selectedDateOption = ref<string | null>('all')

const currencyValue = ref('group')


ChartJS.register(ArcElement, Tooltip, Legend, Colors)
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


import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { computed } from 'vue'
import { useTruncate } from '@/Composables/useTruncate'


const abcdef = computed(() => {
    return props.groupStats.organisations.filter(org => org.type != 'agent').map((org) => {
        return {
            name: org.name,
            code: org.code,
            refunds: org.refunds.number_refunds || 0,
            refunds_diff: 0,
            invoices: org.invoices.number_invoices || 0,
            invoices_diff: get(org, ['sales', `invoices_${selectedDateOption.value}`], 0),
            sales: org.sales?.org_amount_all || 0,
            sales_diff: get(org, ['sales', `org_amount_${selectedDateOption.value}`], 0),
        }
    })
})
</script>

<template>

    <Head :title="trans('Dashboard')" />
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- <pre>{{ props.groupStats.organisations }}</pre> -->

        <!-- Section: Table -->
        <div v-if="true" class="mt-4">

            <div class="">
                <!-- Section: Date options -->
                <div class="block">
                    <nav class="isolate flex rounded border-b border-gray-300" aria-label="Tabs">
                        <div v-for="(interval, idxInterval) in interval_options" :key="idxInterval"
                            @click="() => selectedDateOption = interval.value" :class="[
                                interval.value === selectedDateOption ? '' : 'text-gray-500 hover:text-gray-700',
                            ]"
                            class='relative min-w-0 flex-1 overflow-hidden bg-white hover:bg-gray-100 py-0 text-center text-sm cursor-pointer select-none focus:z-10'>
                            <span>{{ interval.value }}</span>
                            <span aria-hidden="true"
                                :class="[interval.value === selectedDateOption ? 'bg-indigo-500' : 'bg-transparent', 'absolute inset-x-0 bottom-0 h-0.5']" />
                        </div>
                    </nav>
                </div>

                <!-- <pre>{{ abcdef }}</pre> -->

                <div v-if="false" class="mt-2  rounded-t-sm rounded-b-md border border-gray-300 overflow-hidden ">
                    <table class="min-w-full divide-y divide-gray-400">
                        <thead class="">
                            <tr class="text-sm bg-gray-100 relative divide-x divide-gray-200 text-gray-400" :style="{
                                backgroundColor: layout.app.theme[4],
                                color: layout.app.theme[5]
                            }">
                                <!-- Column: Organisations -->
                                <th scope="col" class="w-full pl-4 pr-3 text-left font-normal">
                                    Organisation
                                </th>

                                <!-- Column: Refunds -->
                                <th scope="col" class="px-3 text-left table-cell font-normal w-40 ">
                                    Refunds
                                </th>

                                <!-- Column: Refunds LY -->
                                <Transition>
                                    <th scope="col" class="px-3 text-left sm:table-cell font-normal w-40">
                                        &Delta;{{ trans('1y') }}
                                    </th>
                                </Transition>

                                <!-- Column: Invoices -->
                                <th scope="col" class="px-3 text-left table-cell font-normal w-40 ">
                                    Invoices
                                </th>

                                <!-- Column: Invoices LY -->
                                <Transition>
                                    <th scope="col" class="px-3 text-left sm:table-cell font-normal w-40 ">

                                        &Delta;{{ trans('1y') }}
                                    </th>
                                </Transition>

                                <!-- Column: Sales -->
                                <th scope="col" class="px-3 text-left font-normal w-96 ">
                                    Sales
                                </th>

                                <!-- Column: Sales LY -->
                                <Transition>
                                    <th scope="col" class="px-3 text-left sm:table-cell font-normal w-40 ">
                                        &Delta;{{ trans('1y') }}
                                    </th>
                                </Transition>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            <template v-for="(org, orgIdx) in groupStats.organisations" :key="org.name + orgIdx">
                                <tr v-if="org.type !== 'agent'" class="relative">
                                    <!-- Column: Organisations -->
                                    <td class="w-full max-w-0 pl-4 py-1.5 pr-3 text-sm sm:w-auto sm:max-w-none">
                                        <span v-tooltip="org.name" class="md:hidden">{{ org.code }}</span>
                                        <span class="hidden md:block">{{ org.name }}</span>
                                    </td>

                                    <!-- Column: Refunds -->
                                    <td class="text-sm text-gray-500 table-cell text-right">
                                        <div class="w-24 ">
                                            {{ locale.number(org.refunds.number_refunds || 0) }}
                                        </div>
                                    </td>

                                    <!-- Column: Refunds LY -->
                                    <td class="text-sm text-gray-500 table-cell tabular-nums text-right">
                                        <div v-tooltip="org.interval_percentages?.invoices?.[selectedDateOption || 'all'].difference || undefined"
                                            class="w-12 text-right pl-1 pr-3">
                                            {{ org.interval_percentages?.refunds?.[selectedDateOption ||
                                            'all'].percentage || 0 }}
                                        </div>
                                    </td>

                                    <!-- Column: Invoices -->
                                    <td class="text-sm text-gray-500 table-cell text-right">
                                        <div class="w-32 ">
                                            {{ locale.number(org.invoices.number_invoices || 0) }}
                                        </div>
                                    </td>

                                    <!-- Column: Invoices LY -->
                                    <td class="text-sm text-gray-500 table-cell tabular-nums text-right">
                                        <div v-tooltip="org.interval_percentages?.invoices?.[selectedDateOption || 'all'].difference || undefined"
                                            class="w-12 text-right pl-1 pr-3">
                                            {{ org.interval_percentages?.invoices?.[selectedDateOption ||
                                            'all'].percentage || 0 }}
                                        </div>
                                    </td>

                                    <!-- Column: Sales -->
                                    <td class="overflow-hidden text-sm text-gray-500 table-cell text-right">
                                        <div class="w-32">
                                            <!-- {{ locale.number(org.interval_percentages?.sales?.amount || 0) }} -->
                                            {{ useLocaleStore().currencyFormat(currencyValue === 'organisation' ?
                                            org.currency.code : groupStats.currency.code , get(org, ['sales',
                                            `org_amount_${selectedDateOption}`], 0)) }}
                                        </div>

                                        <!-- <Transition name="spin-to-down" mode="out-in">
                                            <div
                                                class="flex items-center gap-x-1"
                                                :class="
                                                isUpOrDown(org, selectedDateOption) == 'increased' && isShowLastYear
                                                    ? ''
                                                    : isUpOrDown(org, selectedDateOption) == 'decreased' && isShowLastYear
                                                        ? 'text-red-500'
                                                        : ''
                                            " :key="get(org, ['sales', `org_amount_${selectedDateOption}`], 0)">
                                                <Tag v-if="calcPercentage(org, selectedDateOption) && isShowLastYear"
                                                        :theme="
                                                            isUpOrDown(org, selectedDateOption) == 'increased' && isShowLastYear
                                                                ? 3
                                                                : isUpOrDown(org, selectedDateOption) == 'decreased' && isShowLastYear
                                                                    ? 7
                                                                    : 99
                                                        "
                                                        size="xxs"
                                                >
                                                    <template #label>
                                                        <FontAwesomeIcon v-if="isUpOrDown(org, selectedDateOption) == 'increased' && isShowLastYear" icon='fas fa-triangle' size="xs" class='' fixed-width aria-hidden='true' />
                                                        <FontAwesomeIcon v-else-if="isUpOrDown(org, selectedDateOption) == 'decreased' && isShowLastYear" icon='fas fa-triangle' size="xs" class='rotate-180' fixed-width aria-hidden='true' />
                                                        {{ calcPercentage(org, selectedDateOption) }}%
                                                    </template>
                                                </Tag>
                                            </div>
                                        </Transition> -->
                                    </td>

                                    <!-- Column: Sales LY -->
                                    <td class="overflow-hidden text-sm text-gray-500 lg:table-cell">
                                        <div v-tooltip="org.interval_percentages?.sales?.[selectedDateOption || 'all'].difference || undefined"
                                            class="w-12 text-right pl-1 pr-3">
                                            {{ org.interval_percentages?.sales?.[selectedDateOption || 'all'].percentage
                                            || 0 }}
                                        </div>
                                    </td>

                                    <!-- Column: Sales Revenue -->
                                    <!-- <td class="text-sm text-gray-500 lg:table-cell tabular-nums"
                                        :class="
                                            isUpOrDown(org, selectedDateOption) == 'increased'
                                                ? 'text-green-500'
                                                : isUpOrDown(org, selectedDateOption) == 'decreased'
                                                    ? 'text-red-500'
                                                    : 'text-gray-500'
                                        "
                                    >
                                        <FontAwesomeIcon v-if="isUpOrDown(org, selectedDateOption) == 'increased'" icon='fas fa-triangle' size="xs" class='' fixed-width aria-hidden='true' />
                                        <FontAwesomeIcon v-else-if="isUpOrDown(org, selectedDateOption) == 'decreased'" icon='fas fa-triangle' size="xs" class='rotate-180' fixed-width aria-hidden='true' />
                                        {{ calcPercentage(org, selectedDateOption) }}%
                                    </td> -->

                                </tr>
                            </template>
                            <tr>
                                <td>Total</td>
                                <td>Xxxx</td>
                                <td>FFF</td>
                                <td>GGG</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


        </div>

        <div class="mt-6">
            <DataTable :value="abcdef" stripedRows showGridlines removableSort tableStyle="min-width: 50rem">
                <Column field="code" sortable class="overflow-hidden transition-all" header="Code">
                    <template #body="{ data }">
                        <div class="relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.code">
                                    {{ data.code }}
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>

                <Column field="refunds" sortable class="overflow-hidden transition-all" header="Refunds" headerStyle="width: 250px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.refunds">
                                    {{ locale.number(data.refunds || 0) }}
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>

                <Column field="refunds_diff" sortable class="overflow-hidden transition-all" header="&Delta; 1y" headerStyle="width: 130px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.refunds_diff">
                                    {{ locale.number(data.refunds_diff || 0) }}
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>

                <Column field="invoices" sortable class="overflow-hidden transition-all" header="Invoices" headerStyle="text-align: right; width: 200px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.invoices">
                                    {{ locale.number(data.invoices || 0) }}
                                </div>
                            </Transition>
                        </div>
                    </template>

                </Column>

                <Column field="invoices_diff" sortable class="overflow-hidden transition-all" header="&Delta; 1y" headerStyle="width: 130px">
                    <!-- Todo -->
                </Column>

                <Column field="sales" sortable class="overflow-hidden transition-all" header="Sales" headerStyle="width: 250px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.sales">
                                    {{
                                        useLocaleStore().currencyFormat(currencyValue === 'organisation'
                                            ? data.currency.code
                                            : groupStats.currency.code,
                                            data.sales)
                                    }}
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>

                <Column field="sales_diff" sortable class="overflow-hidden transition-all" header="&Delta; 1y" headerStyle="width: 270px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.sales_diff">
                                    {{
                                        useLocaleStore().currencyFormat(currencyValue === 'organisation' ?
                                            org.currency.code : groupStats.currency.code,
                                            data.sales_diff)
                                    }}
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>

            </DataTable>
        </div>

        <div class="mt-10 w-1/2 flex gap-x-4">
            <div class="py-5 px-5 flex gap-x-6 bg-gray-50 rounded-md border border-gray-300 w-fit">
                <div class="w-fit font-semibold py-1 mb-1 text-center">{{ trans('Refunds')}} </div>
                <div class="w-24">
                    <Pie :data="{
                        labels: groupStats.organisations.filter((org) => org.type !== 'agent').map((org) => org.name),
                        datasets: [{
                            data: groupStats.organisations.filter((org) => org.type !== 'agent').map((org) => get(org, ['sales', `org_amount_all`], 0)),
                            hoverOffset: 4
                        }]
                    }" :options="options" />
                </div>
                <!-- <div class="flex flex-col justify-between ">
                    <template v-for="org in groupStats.organisations">
                        <div v-if="org.type !== 'agent'" class="space-x-2">
                            <span class="text-lg">{{ org.code }}:</span>
                            <span class="text-gray-500">{{ useLocaleStore().currencyFormat(currencyValue === 'organisation' ? org.currency.code : groupStats.currency.code, get(org, ['sales', `org_amount_all`], 0)) }}</span>
                        </div>
                    </template>
                </div> -->
            </div>
            <div class="py-5 px-5 flex gap-x-6 bg-gray-50 rounded-md border border-gray-300 w-fit">
                <div class="w-fit font-semibold py-1 mb-1 text-center">{{ trans('Invoices')}} </div>
                <div class="w-24">
                    <Pie :data="{
                        labels: groupStats.organisations.filter((org) => org.type !== 'agent').map((org) => org.name),
                        datasets: [{
                            data: groupStats.organisations.filter((org) => org.type !== 'agent').map((org) => get(org, ['sales', `org_amount_all`], 0)),
                            hoverOffset: 4
                        }]
                    }" :options="options" />
                </div>
                <!-- <div class="flex flex-col justify-between ">
                    <template v-for="org in groupStats.organisations">
                        <div v-if="org.type !== 'agent'" class="space-x-2">
                            <span class="text-lg">{{ org.code }}:</span>
                            <span class="text-gray-500">{{ useLocaleStore().currencyFormat(currencyValue === 'organisation' ? org.currency.code : groupStats.currency.code, get(org, ['sales', `org_amount_all`], 0)) }}</span>
                        </div>
                    </template>
                </div> -->
            </div>
            <div class="py-5 px-5 flex gap-x-6 bg-gray-50 rounded-md border border-gray-300 w-fit">
                <div class="w-fit font-semibold py-1 mb-1 text-center">{{ trans('Sales')}} </div>
                <div class="w-24">
                    <Pie :data="{
                        labels: groupStats.organisations.filter((org) => org.type !== 'agent').map((org) => org.name),
                        datasets: [{
                            data: groupStats.organisations.filter((org) => org.type !== 'agent').map((org) => get(org, ['sales', `org_amount_all`], 0)),
                            hoverOffset: 4
                        }]
                    }" :options="options" />
                </div>
                <!-- <div class="flex flex-col justify-between ">
                    <template v-for="org in groupStats.organisations">
                        <div v-if="org.type !== 'agent'" class="space-x-2">
                            <span class="text-lg">{{ org.code }}:</span>
                            <span class="text-gray-500">{{ useLocaleStore().currencyFormat(currencyValue === 'organisation' ? org.currency.code : groupStats.currency.code, get(org, ['sales', `org_amount_all`], 0)) }}</span>
                        </div>
                    </template>
                </div> -->
            </div>
        </div>
    </div>
</template>