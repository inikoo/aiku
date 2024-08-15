<script setup lang="ts">
import { inject, ref } from 'vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { Switch } from '@headlessui/vue'
import { useLocaleStore } from '@/Stores/locale'
import { RadioGroup, RadioGroupOption } from '@headlessui/vue'
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'



import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Row from 'primevue/row'
import ColumnGroup from 'primevue/columngroup'
import { computed } from 'vue'
import { useTruncate } from '@/Composables/useTruncate'

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
        total: {
            total_invoices: number
            total_refunds: number
            total_sales: string
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
                        amount: string
                        percentage: number
                        difference: number
                    }
                }
                invoices?: {
                    [key: string]: {
                        amount: string
                        percentage: number
                        difference: number
                    }
                }
                refunds?: {
                    [key: string]: {
                        amount: string
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

console.log('asdsadsa', props.groupStats.total)
const layout = inject('layout', layoutStructure)
const locale = inject('locale', {})


// Decriptor: Date interval
const selectedDateOption = ref<string>('all')

// const currencyValue = ref('group')


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



const abcdef = computed(() => {
    return props.groupStats.organisations.filter(org => org.type != 'agent').map((org) => {
        return {
            name: org.name,
            code: org.code,
            interval_percentages: org.interval_percentages,
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



            <!-- Section: Date options -->
            <div class="mt-4 block">
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

                <Column field="refunds" sortable class="overflow-hidden transition-all" header="Refunds" headerStyle="text-align: green; width: 250px" headerClass="bg-red-500">
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

                <Column field="refunds_diff" sortable class="overflow-hidden transition-all" header="&Delta; 1y" headerStyle="text-align: green; width: 130px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <!-- {{ data.interval_percentages?.refunds[selectedDateOption].amount }} -->
                            
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.interval_percentages?.refunds[selectedDateOption].amount">
                                    {{ locale.number(data.interval_percentages?.refunds[selectedDateOption].difference || 0) }} ({{ data.interval_percentages?.refunds[selectedDateOption].percentage || 0 }}%)
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

                <Column field="invoices_diff" sortable class="overflow-hidden transition-all" header="&Delta; 1y" headerStyle="text-align: green; width: 200px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.interval_percentages?.invoices[selectedDateOption].amount">
                                    {{ data.interval_percentages?.invoices[selectedDateOption].difference || 0 }}
                                    ({{ data.interval_percentages?.invoices[selectedDateOption].percentage || 0 }}%)
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>

                <Column field="sales" sortable class="overflow-hidden transition-all" header="Sales" headerStyle="text-align: green; width: 250px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.sales">
                                    {{
                                        useLocaleStore().currencyFormat(groupStats.currency.code, data.sales)
                                    }}
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>

                <Column field="sales_diff" sortable class="overflow-hidden transition-all" header="&Delta; 1y" headerStyle="text-align: green; width: 270px">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.interval_percentages?.refunds[selectedDateOption].amount">
                                    {{ useLocaleStore().currencyFormat( groupStats.currency.code, data.interval_percentages?.refunds[selectedDateOption].difference || 0) }}
                                    ({{ data.interval_percentages?.refunds[selectedDateOption].percentage || 0 }}%)
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>

                <ColumnGroup type="footer">
                    <Row>
                        <Column footer="" footerStyle="text-align:right" />
                        <Column :footer="groupStats.total.total_refunds" footerStyle="text-align:right" />
                        <Column footer="xxx" footerStyle="text-align:right" />
                        <Column :footer="groupStats.total.total_invoices" footerStyle="text-align:right" />
                        <Column footer="xxx" footerStyle="text-align:right" />
                        <Column :footer="useLocaleStore().currencyFormat(groupStats.currency.code, Number(groupStats.total.total_sales))" footerStyle="text-align:right" />
                        <Column footer="xxx" footerStyle="text-align:right" />
                    </Row>
                </ColumnGroup>

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