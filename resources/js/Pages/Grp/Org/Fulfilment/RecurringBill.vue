<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { computed, defineAsyncComponent, ref } from 'vue'
import type { Component } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'

import StartEndDate from '@/Components/Utils/StartEndDate.vue'
import RecurringBillTransactions from '@/Pages/Grp/Org/Fulfilment/RecurringBillTransactions.vue'
import BoxStatsRecurringBills from '@/Components/Fulfilment/BoxStatsRecurringBills.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { compareAsc } from 'date-fns'
import { routeType } from '@/types/route'

// import TablePallets from '@/Components/Tables/Grp/Org/Fulfilment/TablePallets.vue'
// import type { Timeline } from '@/types/Timeline'
import { useDaysLeftFromToday } from '@/Composables/useFormatTime'


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faWaveSine } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
import { trans } from 'laravel-vue-i18n'
library.add(faWaveSine)


const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs
    // showcase: {}
    // pallets: {}
    transactions: {}
    status_rb: string
    updateRoute: routeType
    timeline_rb: {
        start_date: string
        end_date: string
    }
    box_stats: {}
    consolidateRoute: routeType


}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        transactions: RecurringBillTransactions,
        // pallets: TablePallets
    }

    return components[currentTab.value]
})

console.log(props)
</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <!-- <pre>{{ props.box_stats }}</pre> -->

    <!-- Section: Timeline -->
    <!-- <div class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
        <Timeline :options="timeline_rb" :slidesPerView="6" />
    </div> -->

    <div class="py-4 px-3">
        <div class="grid sm:grid-cols-2 sm:divide-x divide-gray-500/30 gap-y-6 h-full w-full rounded-md px-4 py-2"
            :class="[status_rb === 'current' ? 'bg-green-100 ring-1 ring-green-500 text-green-700' : 'bg-gray-200 ring-1 ring-gray-500 text-gray-700']"
        >
            <div class="flex flex-col lg:flex-row w-full justify-start lg:justify-between items-start lg:items-center gap-y-2 pr-4">
                <div class="flex flex-col justify-center ">
                    <!-- <div class="text-xs">Status</div> -->
                    <div class="font-semibold capitalize text-lg">
                        {{ status_rb === 'current' ? trans('On going') : trans('Expired') }}
                        <FontAwesomeIcon icon='far fa-wave-sine' class='' fixed-width aria-hidden='true' />
                    </div>
                    <div v-if="status_rb === 'current'" class="flex gap-x-1 text-xs italic text-green-700/70">
                        <div>End date is</div>
                        <div>
                            <Transition name="spin-to-down">
                                <span :key="timeline_rb.end_date">{{ useDaysLeftFromToday(timeline_rb.end_date) }}</span>
                            </Transition>
                        </div>
                    </div>
                </div>
                
                <component
                    v-if="compareAsc(new Date(timeline_rb.end_date), new Date()) === 1 && status_rb === 'current'" class=""
                    :is="consolidateRoute?.name ? Link : 'div'"
                    :href="consolidateRoute?.name ? route(consolidateRoute.name, consolidateRoute.parameters) : '#'"
                    :method="consolidateRoute?.method"
                >
                    <Button label="Consolidate now" />
                </component>
            </div>
            
            <div class="sm:pl-6 pr-0">
                <StartEndDate
                    :startDate="timeline_rb.start_date"
                    :endDate="timeline_rb.end_date"
                    :updateRoute
                />
            </div>

        </div>
        
    </div>

    <BoxStatsRecurringBills :boxStats="box_stats" />

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />
</template>
