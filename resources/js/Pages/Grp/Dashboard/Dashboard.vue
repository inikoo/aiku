<script setup lang="ts">
import { inject, ref } from 'vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { Switch } from '@headlessui/vue'
import { useLocaleStore } from '@/Stores/locale'
import { RadioGroup, RadioGroupOption } from '@headlessui/vue'

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
        organisations: [
            {
                currency: {
                    code: string
                }
                type: string
                name: string
                code: string
                number_invoices_type_refund?: number
                number_invoices?: number
                number_invoices_type_invoice?: number
            }
        ]
    }
}>()

const layout = inject('layout', layoutStructure)
// console.log('asdsadsa', layout.organisations)

const isShowLastYear = ref(true)

// Decriptor: Date interval
const selectedDateOption = ref<string | null>('all')
const dateOptions = [
    {
        label: trans('Year to date'),
        labelShort: trans('Ytd'),
        value: 'ytd'
    },
    {
        label: trans('Quarter to date'),
        labelShort: trans('Qtd'),
        value: 'qtd'
    },
    {
        label: trans('Month to date'),
        labelShort: trans('Mtd'),
        value: 'mtd'
    },
    {
        label: trans('Week to date'),
        labelShort: trans('Wtd'),
        value: 'wtd'
    },
    {
        label: trans('Last month'),
        labelShort: trans('lm'),
        value: 'lm'
    },
    {
        label: trans('Last week'),
        labelShort: trans('lw'),
        value: 'lw'
    },
    {
        label: trans('Yesterday'),
        labelShort: trans('y'),
        value: 'yda'
    },
    {
        label: trans('Today'),
        labelShort: trans('t'),
        value: 'tdy'
    },
    {
        label: trans('1 Year'),
        labelShort: trans('1y'),
        value: '1y'
    },
    {
        label: trans('1 Quarter'),
        labelShort: trans('1q'),
        value: '1q'
    },
    {
        label: trans('1 Month'),
        labelShort: trans('1m'),
        value: '1m'
    },
    {
        label: trans('1 Week'),
        labelShort: trans('1w'),
        value: '1w'
    },
    {
        label: trans('All'),
        labelShort: trans('All'),
        value: 'all'
    },

]

const currencyValue = ref('group')

const selectedTabGraph = ref(0)
const tabs = [
    { name: 'My Account', href: '#', current: true },
    { name: 'Company', href: '#', current: false },
    { name: 'Team Members', href: '#', current: false },
    { name: 'Billing', href: '#', current: false },
]

// Method: to check the data is increase of decrease based on last year data
const isUpOrDown = (orgData: {}, keyName: string | null): string => {
    const currentNumber = parseFloat(get(orgData, ['sales', `org_amount_${keyName}`], 0))
    const lastyearNumber = parseFloat(get(orgData, ['sales', `org_amount_${keyName}_ly`], 0))
    
    if (!currentNumber) return 'nodata'
    
    else if (lastyearNumber > currentNumber) {
        return 'decreased'
    } else if (lastyearNumber < currentNumber) {
        return 'increased'
    } else {
        return 'same'
    }
}

// Method: to retrive the percentage based on last year data
const calcPercentage = (orgData: {}, keyName: string | null) => {
    const currentNumber = parseFloat(get(orgData, ['sales', `org_amount_${keyName}`], 0))
    const lastyearNumber = parseFloat(get(orgData, ['sales', `org_amount_${keyName}_ly`], 0))

    // console.log(currentNumber, lastyearNumber)

    if (!currentNumber && lastyearNumber) {
        return -100 // Percentage change is infinite if currentNumber is 0
    } else if (currentNumber && !lastyearNumber) {
        return 100
    } else if (!currentNumber && !lastyearNumber) {
        return 0
    }

    return ((lastyearNumber - currentNumber) / currentNumber) * 100
}


const groupCurrency = [
    {
        name: 'group',
        label: 'Group',
        icon: ''
    },
    {
        name: 'organisation',
        label: 'Org',
        icon: ''
    }
]
</script>

<template>
    <Head :title="trans('Dashboard')" />

    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <!-- <pre>{{ layout.organisations.data }}</pre> -->

        <!-- Section: Table -->
        <div class="mt-8">

            <div v-if="false" class="grid grid-cols-2">
                <!-- Sections: Tabs -->
                <nav v-if="true" class="rounded-md overflow-hidden isolate flex divide-x divide-gray-200 border border-gray-100 w-fit" aria-label="Tabs">
                    <div v-for="(tab, tabIdx) in tabs" :key="tab.name"
                        @click="selectedTabGraph = tabIdx"
                        class="group relative flex-1 py-2 px-4 text-center text-sm font-medium focus:z-10 cursor-pointer transition-all"
                        :class="[
                            selectedTabGraph == tabIdx? 'bg-indigo-500 text-white' : 'hover:bg-indigo-50 text-gray-500 hover:text-gray-600',
                        ]"
                    >
                        <span class="whitespace-nowrap select-none">{{ tab.name }}</span>
                        <!-- <span aria-hidden="true"
                            :class="[selectedTabGraph == tabIdx ? 'bottomNavigationActive' : 'bottomNavigation', 'h-0.5']" /> -->
                    </div>
                </nav>

                <div class="flex flex-col gap-y-8 gap-x-4 justify-between w-full">
                    
                    <!-- <div class="w-44">
                        <PureMultiselect v-model="dateValue" required :options="dateOptions" caret object />
                    </div> -->

                </div>

                <div class="justify-self-end flex divide-x divide-gray-300 gap-x-4">
                    <!-- Radio: Show Last Year -->
                    <div v-if="true" class="justify-self-end flex items-center gap-x-2 text-sm">
                        <!-- <div :class="!isShowLastYear ? '' : 'text-gray-400'">Don't show Last year</div> -->
                        <Switch
                            v-model="isShowLastYear"
                            :class="isShowLastYear ? 'bg-indigo-500' : 'bg-indigo-100'"
                            class="relative inline-flex h-[25px] w-[61px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                            >
                            <span
                                aria-hidden="true"
                                :class="isShowLastYear ? 'translate-x-9' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-[21px] aspect-square transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                            />
                        </Switch>
                        <div @click="isShowLastYear = !isShowLastYear" class="select-none cursor-pointer whitespace-nowrap" :class="isShowLastYear ? '' : 'text-gray-400'">Show Last Year</div>
                    </div>
                    
                </div>
            </div>

            
            <div class="border border-gray-300 rounded-t-lg">
                <table class="min-w-full divide-y divide-gray-400">
                    <thead class="">
                        <tr class="rounded-full">
                            <!-- Radio: Show currency group/org -->
                            <td colspan="4" class="px-2 py-2">
                                <div class="flex items-center gap-x-2 text-sm">
                                    <div @click="currencyValue = 'group'"
                                        class="px-1 select-none cursor-pointer whitespace-nowrap"
                                        :class="currencyValue === 'group' ? 'text-indigo-600' : 'text-gray-400'">
                                        {{ useGetCurrencySymbol(groupStats.currency.code) }}
                                    </div>
                                    <div class="border border-indigo-300 w-fit rounded-full overflow-hidden">
                                        <RadioGroup v-model="currencyValue" class="grid grid-cols-2">
                                            <RadioGroupOption v-for="curr in groupCurrency" as="template" :key="curr.name" :value="curr.name" v-slot="{ active, checked }">
                                                <div class="select-none cursor-pointer focus:outline-none flex items-center justify-center py-2 px-3 text-xs font-semibold uppercase sm:flex-1"
                                                    :class="[checked ? 'bg-indigo-200 hover:bg-indigo-300' : 'bg-white hover:bg-indigo-50']">
                                                    {{ curr.label }}
                                                </div>
                                            </RadioGroupOption>
                                        </RadioGroup>
                                    </div>
                                    <div @click="currencyValue = 'organisation'"
                                        class="select-none cursor-pointer whitespace-nowrap"
                                        :class="currencyValue === 'organisation' ? 'text-indigo-600' : 'text-gray-400'">
                                        <span v-for="org in [...new Set(groupStats.organisations.filter(org => org.type != 'agent').map(org => org.currency.code))]">
                                            {{ useGetCurrencySymbol(org) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                
                            <td colspan="4" class="px-2 py-2">
                                <div class="flex flex-wrap justify-end gap-x-1.5 gap-y-2">
                                    <div v-for="(xxxdate, idxXxxdate) in dateOptions" :key="xxxdate.value + idxXxxdate"
                                        @click="() => selectedDateOption = xxxdate.value"
                                        v-tooltip="xxxdate.label"
                                        class="h-fit text-xs select-none whitespace-nowrap py-1 px-2.5 rounded-md w-fit cursor-pointer"
                                        :class="xxxdate.value === selectedDateOption ? 'bg-indigo-500 text-white border border-transparent' : 'border border-gray-200  hover:bg-gray-200'"
                                    >
                                        {{ xxxdate.value }}
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr  class="bg-gray-100 relative divide-x divide-gray-200 text-gray-400"
                            :style="{
                                backgroundColor: layout.app.theme[4],
                                color: layout.app.theme[5]
                            }"
                        >
                            <!-- Column: Organisations -->
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left font-normal">
                                Organisation
                            </th>
                
                            <!-- Column: Refunds -->
                            <th scope="col" class="px-3 py-3.5 text-left table-cell font-normal">
                                Refunds
                            </th>
                
                            <!-- Column: Refunds LY -->
                            <Transition>
                                <th scope="col" class="px-3 py-3.5 text-left sm:table-cell font-normal">
                                    Refunds vs. last year
                                </th>
                            </Transition>
                
                            <!-- Column: Invoices -->
                            <th scope="col" class="px-3 py-3.5 text-left table-cell font-normal">
                                Invoices
                            </th>
                
                            <!-- Column: Invoices LY -->
                            <Transition>
                                <th scope="col" class="px-3 py-3.5 text-left sm:table-cell font-normal">
                                    Invoices vs. last year
                                </th>
                            </Transition>
                
                            <!-- Column: Sales -->
                            <th scope="col" class="min-w-40 px-3 py-3.5 text-left font-normal">
                                Sales
                            </th>
                
                            <!-- Column: Sales LY -->
                            <Transition>
                                <th scope="col" class="px-3 py-3.5 text-left sm:table-cell font-normal">
                                    vs. last year
                                </th>
                            </Transition>
                
                            <!-- Column: Actions -->
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                <span class="sr-only">Edit</span>
                            </th>
                        </tr>
                    </thead>
                
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <template v-for="(org, orgIdx) in groupStats.organisations" :key="org.name + orgIdx">
                            <tr v-if="org.type !== 'agent'" class="relative">
                                <!-- Column: Organisations -->
                                <td class="uppercase w-full max-w-0 py-4 pl-4 pr-3 text-sm sm:w-auto sm:max-w-none">
                                    <span v-tooltip="org.name">{{ org.code }}</span>
                                </td>
                
                                <!-- Column: Refunds -->
                                <td class="px-3 py-4 text-sm text-gray-500 table-cell">{{ org.number_invoices_type_refund || 0 }}</td>
                
                                <!-- Column: Refunds LY -->
                                <td class="px-3 py-4 text-sm text-gray-500 table-cell tabular-nums">
                                    ----
                                </td>
                
                                <!-- Column: Invoices -->
                                <td class="px-3 py-4 text-sm text-gray-500 table-cell">{{ org.number_invoices || 0 }}</td>
                
                                <!-- Column: Invoices LY -->
                                <Transition>
                                    <td class="px-3 py-4 text-sm text-gray-500 table-cell tabular-nums">
                                        {{ org.number_invoices_type_invoice }}
                                    </td>
                                </Transition>
                
                                <!-- Column: Sales -->
                                <td class="overflow-hidden px-3 py-4 text-sm text-gray-500 table-cell">
                                    <Transition name="spin-to-down" mode="out-in">
                                        <div
                                            class="flex items-center gap-x-1"
                                            :class="
                                            isUpOrDown(org, selectedDateOption) == 'increased' && isShowLastYear
                                                ? ''
                                                : isUpOrDown(org, selectedDateOption) == 'decreased' && isShowLastYear
                                                    ? 'text-red-500'
                                                    : ''
                                        " :key="get(org, ['sales', `org_amount_${selectedDateOption}`], 0)">
                                            {{ useLocaleStore().currencyFormat(currencyValue === 'organisation' ? org.currency.code : groupStats.currency.code , get(org, ['sales', `org_amount_${selectedDateOption}`], 0)) }}
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
                                    </Transition>
                                </td>
                                <!-- Column: Sales LY -->
                                <td class="overflow-hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                                    <Transition name="spin-to-down" mode="out-in">
                                        <div class="" :key="get(org, ['sales', `org_amount_${selectedDateOption+'_ly'}`], 0)">
                                            <!-- groupStats.currency.code == 'GBP' -->
                                            <!-- org.currency.code == 'INR' -->
                                            {{ useLocaleStore().currencyFormat(currencyValue  === 'organisation' ? org.currency.code : groupStats.currency.code , get(org, ['sales', `org_amount_${selectedDateOption+'_ly'}`], 0)) }}
                                        </div>
                                    </Transition>
                                </td>
                
                                <!-- Column: Sales Revenue -->
                                <!-- <td class="px-3 py-4 text-sm text-gray-500 lg:table-cell tabular-nums"
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
                
                                <!-- Column: Actions -->
                                <td class="py-4 pl-3 pr-4 text-right text-sm font-medium">
                                    <!-- <a href="#" class="text-indigo-600 hover:text-indigo-900">
                                        <Button label="open" />
                                    </a> -->
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
          <!--  <pre>{{ groupStats.organisations[1] }}</pre> -->

            
        </div>
    </div>
</template>