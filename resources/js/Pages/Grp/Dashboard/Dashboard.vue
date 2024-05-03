<script setup lang="ts">
import { inject, ref } from 'vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { Switch } from '@headlessui/vue'
import { useLocaleStore } from '@/Stores/locale'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowUp, faArrowDown, faChevronDown } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Head } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { get } from 'lodash'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import Button from '@/Components/Elements/Buttons/Button.vue'
library.add(faArrowUp, faArrowDown, faChevronDown)

const props = defineProps<{
    groupStats: {}
}>()

const layout = inject('layout', layoutStructure)
// console.log('asdsadsa', layout.organisations)

const isShowLastYear = ref(true)

// Decriptor: Date interval
const selectedDateOption = ref<string | null>('all')
const dateOptions = [
    {
        label: trans('Year to date'),
        value: 'ytd'
    },
    {
        label: trans('Quarter to date'),
        value: 'qtd'
    },
    {
        label: trans('Month to date'),
        value: 'mtd'
    },
    {
        label: trans('Week to date'),
        value: 'wtd'
    },
    {
        label: trans('Last month'),
        value: 'lm'
    },
    {
        label: trans('Last week'),
        value: 'lw'
    },
    {
        label: trans('Yesterday'),
        value: 'yda'
    },
    {
        label: trans('Today'),
        value: 'tdy'
    },
    {
        label: trans('1 Year'),
        value: '1y'
    },
    {
        label: trans('1 Quarter'),
        value: '1q'
    },
    {
        label: trans('1 Month'),
        value: '1m'
    },
    {
        label: trans('1 Week'),
        value: '1w'
    },
    {
        label: trans('All'),
        value: 'all'
    },
    // {
    //     label: 'xxxxxxxxxxx',
    //     value: 'xxxxxxxxxxx'
    // },
]

const dateValue = ref({
    label: trans('All'),
    value: 'all'
})
const currencyValue = ref(true)

const selectedTabGraph = ref(0)
const tabs = [
    { name: 'My Account', href: '#', current: true },
    { name: 'Company', href: '#', current: false },
    { name: 'Team Members', href: '#', current: false },
    { name: 'Billing', href: '#', current: false },
]

// Method: to check the data is increase of decrease based on last year data
const isUpOrDown = (orgData, keyName: string | null): string => {
    const currentRevenue = parseFloat(get(orgData, ['sales', `org_amount_${keyName}`], 0))
    const lastyearRevenue = parseFloat(get(orgData, ['sales', `org_amount_${keyName}_ly`], 0))
    
    if (!currentRevenue) return 'nodata'
    
    else if (lastyearRevenue > currentRevenue) {
        return 'up'
    } else if (lastyearRevenue < currentRevenue) {
        return 'down'
    } else {
        return 'same'
    }
}

// Method: to retrive the percentage based on last year data
const calcPercentage = (orgData, keyName: string | null) => {
    const currentRevenue = parseFloat(get(orgData, ['sales', `org_amount_${keyName}`], 0))
    const lastyearRevenue = parseFloat(get(orgData, ['sales', `org_amount_${keyName}_ly`], 0))

    // console.log(currentRevenue, lastyearRevenue)

    if (!currentRevenue && lastyearRevenue) {
        return -100 // Percentage change is infinite if currentRevenue is 0
    } else if (currentRevenue && !lastyearRevenue) {
        return 100
    } else if (!currentRevenue && !lastyearRevenue) {
        return 0
    }

    return ((lastyearRevenue - currentRevenue) / currentRevenue) * 100
}

</script>

<template>
    <Head :title="trans('Dashboard')" />

    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <!-- Section: Table -->
        <div class="mt-8">

            <div class="grid grid-cols-2 ">
                <!-- Sections: Tabs -->
                <div class="flex justify-between items-center">
                    <nav v-if="false" class="rounded-md overflow-hidden isolate flex divide-x divide-gray-200 border border-gray-100 w-fit" aria-label="Tabs">
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
                        <!-- Radio: Show currency group/org -->
                        <div class="flex items-center gap-x-2 font-semibold">
                            <div :class="!currencyValue ? '' : 'text-gray-400'">Group Currency</div>
                            <Switch
                                v-model="currencyValue"
                                :class="currencyValue ? 'bg-indigo-500' : 'bg-indigo-100'"
                                class="relative inline-flex h-[25px] w-[61px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                                >
                                <span class="sr-only">Use setting</span>
                                <span
                                    aria-hidden="true"
                                    :class="currencyValue ? 'translate-x-9' : 'translate-x-0'"
                                    class="pointer-events-none inline-block h-[21px] aspect-square transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                                />
                            </Switch>
                            <div :class="currencyValue ? '' : 'text-gray-400'">Org Currency</div>
                        </div>
                        <!-- <div class="w-44">
                            <PureMultiselect v-model="dateValue" required :options="dateOptions" caret object />
                        </div> -->

                        <!-- Radio: Show Last Year -->
                        <div class="flex items-center gap-x-2 font-semibold">
                            <div :class="!isShowLastYear ? '' : 'text-gray-400'">Don't show Last year</div>
                            <Switch
                                v-model="isShowLastYear"
                                :class="isShowLastYear ? 'bg-indigo-500' : 'bg-indigo-100'"
                                class="relative inline-flex h-[25px] w-[61px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                                >
                                <span class="sr-only">Use setting</span>
                                <span
                                    aria-hidden="true"
                                    :class="isShowLastYear ? 'translate-x-9' : 'translate-x-0'"
                                    class="pointer-events-none inline-block h-[21px] aspect-square transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                                />
                            </Switch>
                            <div :class="isShowLastYear ? '' : 'text-gray-400'">Show Last Year</div>
                        </div>
                    </div>
                </div>
                
                <!-- List: Options of date -->
                <div class="border border-gray-300">
                    <div class="text-center bg-indigo-500 text-white">Options of date</div>
                    <div class="px-2 py-2 flex flex-wrap justify-center gap-x-1 gap-y-2">
                        <div v-for="(xxxdate, idxXxxdate) in dateOptions" :key="xxxdate.value + idxXxxdate"
                            @click="() => selectedDateOption = xxxdate.value"
                            class="select-none whitespace-nowrap py-1 px-2.5 rounded-md w-fit cursor-pointer"
                            :class="xxxdate.value === selectedDateOption ? 'bg-indigo-500 text-white border border-transparent' : 'border border-gray-200  hover:bg-gray-200'"
                        >
                            {{ xxxdate.label }}
                        </div>
                    </div>
                </div>
            </div>

            <table class="border border-gray-300 rounded mt-5 min-w-full divide-y divide-gray-400">
                <thead class="bg-gray-100">
                    <tr class="relative divide-x divide-gray-200 text-gray-400">
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left font-normal">
                            Organisation
                        </th>
                        <!-- <th scope="col" class="px-3 py-3.5 text-left hidden lg:table-cell font-normal">
                            Refunds
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left sm:table-cell font-normal">
                            Refunds {{ dateValue?.label }}
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left hidden lg:table-cell font-normal">
                            Invoices
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left sm:table-cell font-normal">
                            Invoices {{ dateValue?.label }}
                        </th> -->
                        <!-- <template v-for="(xxxdate, idxXxxdate) in dateOptions" :key="xxxdate.value + idxXxxdate"> -->
                        <th scope="col" class="min-w-40 px-3 py-3.5 text-left font-normal">
                            Revenue
                        </th>
                        <Transition >
                            <th v-if="isShowLastYear" scope="col" class="px-3 py-3.5 text-left sm:table-cell font-normal">
                                vs. last year
                            </th>
                        </Transition>
                        <!-- </template> -->
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                            Sales revenue
                        </th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    <template v-for="(org, orgIdx) in groupStats.organisations" :key="org.name + orgIdx">
                        <tr v-if="org.type !== 'agent'">
                            <!-- <pre>{{ org.type }}</pre> -->
                            <!-- Column: Store -->
                            <td class="uppercase w-full max-w-0 py-4 pl-4 pr-3 text-sm font-medium sm:w-auto sm:max-w-none">
                                <span v-tooltip="org.name">{{ org.code }}</span>
                                <!-- <dl class="font-normal md:hidden">
                                    <dd class="mt-1 truncate text-gray-500">Refunds {{ org.refunds }}</dd>
                                    <dd class="mt-1 truncate text-gray-500">Invoice: {{ org.invoices }}</dd>
                                    <dd class="mt-1 truncate text-gray-500">Sales: {{ org.group_amount_1m }}</dd>
                                </dl> -->
                            </td>
                        
                            <!-- Column: Refunds -->
                            <!-- <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ org.refunds }}</td> -->
                        
                            <!-- Column: Refunds Revenue -->
                            <!-- <td class="px-3 py-4 text-sm text-gray-500 lg:table-cell tabular-nums" :class="{'text-green-500': org.refundsRevenue.status == 'up', 'text-red-500': org.refundsRevenue.status == 'down'}" >
                                <FontAwesomeIcon v-if="org.refundsRevenue.status == 'up'" icon='far fa-arrow-up' class='' fixed-width aria-hidden='true' />
                                <FontAwesomeIcon v-else-if="org.refundsRevenue.status == 'down'" icon='far fa-arrow-down' class='' fixed-width aria-hidden='true' />
                                {{ org.refundsRevenue.value }}
                            </td> -->
                        
                            <!-- Column: Invoices -->
                            <!-- <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ org.invoices }}</td> -->
                        
                            <!-- Column: Invoices Revenue -->
                            <!-- <td class="px-3 py-4 text-sm text-gray-500 lg:table-cell tabular-nums" :class="{'text-green-500': org.invoicesRevenue.status == 'up', 'text-red-500': org.invoicesRevenue.status == 'down'}" >
                                <FontAwesomeIcon v-if="org.invoicesRevenue.status == 'up'" icon='far fa-arrow-up' class='' fixed-width aria-hidden='true' />
                                <FontAwesomeIcon v-else-if="org.invoicesRevenue.status == 'down'" icon='far fa-arrow-down' class='' fixed-width aria-hidden='true' />
                                {{ org.invoicesRevenue.value }}
                            </td> -->
                        
                            <!-- Column: Sales -->
                            <!-- <template v-for="(xxxdate, idxXxxdate) in dateOptions" :key="xxxdate.value + idxXxxdate"> -->
                            <td class="overflow-hidden hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                                <Transition name="spin-to-down" mode="out-in">
                                    <div class="" :key="get(org, ['sales', `org_amount_${selectedDateOption}`], 0)">{{ useLocaleStore().currencyFormat(currencyValue ? org.currency.code : groupStats.currency.code , get(org, ['sales', `org_amount_${selectedDateOption}`], 0)) }}</div>
                                </Transition>
                            </td>

                            <Transition>
                                <td v-if="isShowLastYear" class="overflow-hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                                    <Transition name="spin-to-down" mode="out-in">
                                        <div class="" :key="get(org, ['sales', `org_amount_${selectedDateOption+'_ly'}`], 0)">
                                            {{ useLocaleStore().currencyFormat(currencyValue ? org.currency.code : groupStats.currency.code , get(org, ['sales', `org_amount_${selectedDateOption+'_ly'}`], 0)) }}
                                        </div>
                                    </Transition>
                                </td>
                            </Transition>
                            <!-- </template> -->
                        
                            <!-- Column: Sales Revenue -->
                            <td class="px-3 py-4 text-sm text-gray-500 lg:table-cell tabular-nums"
                                :class="
                                isUpOrDown(org, selectedDateOption) == 'up'
                                    ? 'text-green-500'
                                    : isUpOrDown(org, selectedDateOption) == 'down'
                                        ? 'text-red-500'
                                        : 'text-gray-500'
                                "
                            >
                                <FontAwesomeIcon v-if="isUpOrDown(org, selectedDateOption) == 'up'" icon='far fa-arrow-up' class='' fixed-width aria-hidden='true' />
                                <FontAwesomeIcon v-else-if="isUpOrDown(org, selectedDateOption) == 'down'" icon='far fa-arrow-down' class='' fixed-width aria-hidden='true' />
                                {{ calcPercentage(org, selectedDateOption) }}%
                            </td>
                        
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
            <!-- <pre>{{ groupStats.organisations[3] }}</pre> -->

            
        </div>
    </div>
</template>