<script setup lang="ts">
import { ref } from 'vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { Switch } from '@headlessui/vue'
import { useLocaleStore } from '@/Stores/locale'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowUp, faArrowDown, faChevronDown } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Head } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { get } from 'lodash'
library.add(faArrowUp, faArrowDown, faChevronDown)

const props = defineProps<{
    groupStats: {}
}>()

// console.log('asdsadsa', props.groupStats)

// Decriptor: Date interval
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
const isUpOrDown = (stat): string => {
    if (get(stat, ['sales', `org_amount_${dateValue.value.value}_ly`], 0)) return 'nodata'
    else if (get(stat, ['sales', `org_amount_${dateValue.value.value}`], 0) > get(stat, ['sales', `org_amount_${dateValue.value.value}_ly`], 0)) {
        return 'up'
    } else if (get(stat, ['sales', `org_amount_${dateValue.value.value}`], 0) < get(stat, ['sales', `org_amount_${dateValue.value.value}_ly`], 0)) {
        return 'down'
    } else {
        return 'same'
    }
}

// Method: to retrive the percentage based on last year data
const calcPercentage = (stat) => {
    if(get(stat, ['sales', `org_amount_${dateValue.value}_ly`], 0) == 0) {
        return '-'
    } else {
        return get(stat, ['sales', `org_amount_${dateValue.value}`], 0)/get(stat, ['sales', `org_amount_${dateValue.value}_ly`], 0) * 100 
    }
    
}
</script>

<template>
    <Head :title="trans('Dashboard')" />

    <div class="px-4 sm:px-6 lg:px-8 py-6">


        <!-- Section: Table -->
        <div class="mt-8">
            <!-- Sections: Tabs -->
            <div class="flex justify-between items-center">
                <nav v-if="false" class="rounded-md overflow-hidden isolate flex divide-x divide-gray-200 border border-gray-100 w-fit" aria-label="Tabs">
                    <div v-for="(tab, tabIdx) in tabs" :key="tab.name"
                        @click="selectedTabGraph = tabIdx"
                        :class="[
                            selectedTabGraph == tabIdx? 'bg-indigo-500 text-white' : 'hover:bg-indigo-50 text-gray-500 hover:text-gray-600',
                        ]"
                        class="group relative flex-1 py-2 px-4 text-center text-sm font-medium focus:z-10 cursor-pointer transition-all"
                    >
                        <span class="whitespace-nowrap select-none">{{ tab.name }}</span>
                        <!-- <span aria-hidden="true"
                            :class="[selectedTabGraph == tabIdx ? 'bottomNavigationActive' : 'bottomNavigation', 'h-0.5']" /> -->
                    </div>
                </nav>

                <div class="flex gap-x-4">
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
                    <div class="w-44">
                        <PureMultiselect v-model="dateValue" required :options="dateOptions" caret object />
                    </div>
                </div>
            </div>

            <table class="border border-gray-300 rounded mt-5 min-w-full divide-y divide-gray-400">
                <thead class="bg-gray-100">
                    <tr class="divide-x divide-gray-200 text-gray-400">
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
                        <th scope="col" class="px-3 py-3.5 text-left hidden lg:table-cell font-normal">
                            Sales
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left sm:table-cell font-normal">
                            Sales {{ dateValue?.label }}
                        </th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="(stat, statIdx) in groupStats.organisations" :key="stat.name + statIdx">
                        <!-- <pre>{{stat}}</pre> -->
                        <!-- Column: Store -->
                        <td class="w-full max-w-0 py-4 pl-4 pr-3 text-sm font-medium sm:w-auto sm:max-w-none">
                            {{ stat.name }}
                            <!-- <dl class="font-normal md:hidden">
                                <dd class="mt-1 truncate text-gray-500">Refunds {{ stat.refunds }}</dd>
                                <dd class="mt-1 truncate text-gray-500">Invoice: {{ stat.invoices }}</dd>
                                <dd class="mt-1 truncate text-gray-500">Sales: {{ stat.group_amount_1m }}</dd>
                            </dl> -->
                        </td>
                        
                        <!-- Column: Refunds -->
                        <!-- <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ stat.refunds }}</td> -->
                        
                        <!-- Column: Refunds Revenue -->
                        <!-- <td class="px-3 py-4 text-sm text-gray-500 lg:table-cell tabular-nums" :class="{'text-green-500': stat.refundsRevenue.status == 'up', 'text-red-500': stat.refundsRevenue.status == 'down'}" >
                            <FontAwesomeIcon v-if="stat.refundsRevenue.status == 'up'" icon='far fa-arrow-up' class='' fixed-width aria-hidden='true' />
                            <FontAwesomeIcon v-else-if="stat.refundsRevenue.status == 'down'" icon='far fa-arrow-down' class='' fixed-width aria-hidden='true' />
                            {{ stat.refundsRevenue.value }}
                        </td> -->
                        
                        <!-- Column: Invoices -->
                        <!-- <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ stat.invoices }}</td> -->
                        
                        <!-- Column: Invoices Revenue -->
                        <!-- <td class="px-3 py-4 text-sm text-gray-500 lg:table-cell tabular-nums" :class="{'text-green-500': stat.invoicesRevenue.status == 'up', 'text-red-500': stat.invoicesRevenue.status == 'down'}" >
                            <FontAwesomeIcon v-if="stat.invoicesRevenue.status == 'up'" icon='far fa-arrow-up' class='' fixed-width aria-hidden='true' />
                            <FontAwesomeIcon v-else-if="stat.invoicesRevenue.status == 'down'" icon='far fa-arrow-down' class='' fixed-width aria-hidden='true' />
                            {{ stat.invoicesRevenue.value }}
                        </td> -->
                        
                        <!-- Column: Sales -->
                        <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                            {{ useLocaleStore().currencyFormat(currencyValue ? stat.currency.code : groupStats.currency.code , get(stat, ['sales', `org_amount_${dateValue.value}`], 0)) }}
                        </td>
                        
                        <!-- Column: Sales Revenue -->
                        <td class="px-3 py-4 text-sm text-gray-500 lg:table-cell tabular-nums"
                            :class="
                            isUpOrDown(stat) == 'up'
                                ? 'text-green-500'
                                : isUpOrDown(stat) == 'down'
                                    ? 'text-red-500'
                                    : 'text-gray-500'
                            "
                        >
                            <FontAwesomeIcon v-if="isUpOrDown(stat) == 'up'" icon='far fa-arrow-up' class='' fixed-width aria-hidden='true' />
                            <FontAwesomeIcon v-else-if="isUpOrDown(stat) == 'down'" icon='far fa-arrow-down' class='' fixed-width aria-hidden='true' />
                            {{ calcPercentage(stat) }}
                        </td>
                                                
                        <!-- Column: Actions -->
                        <td class="py-4 pl-3 pr-4 text-right text-sm font-medium">
                            <a v-if="false" href="#" class="text-indigo-600 hover:text-indigo-900">
                                Edit
                                <span class="sr-only">, {{ stat.name }}</span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            
        </div>
    </div>
</template>
