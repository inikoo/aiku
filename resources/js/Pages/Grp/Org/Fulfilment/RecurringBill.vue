<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
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

// import TablePallets from '@/Components/Tables/Grp/Org/Fulfilment/TablePallets.vue'
// import type { Timeline } from '@/types/Timeline'


const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs
    showcase: {}
    pallets: {}
    transactions: {}
    status_rb: string
    timeline_rb: {
        start_date: string
        end_date: string
    }
    box_stats: {}


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

    <div class="grid grid-cols-2">
        <div class="py-4 px-3">
            <div class="flex flex-col justify-center h-full w-full rounded-md px-4 py-2"
                :class="[status_rb === 'current' ? 'bg-green-100 ring-1 ring-green-500 text-green-700' : 'bg-gray-200 ring-1 ring-gray-500 text-gray-700']"
            >
                <div class="text-xs">Status</div>
                <div class="font-semibold capitalize">{{ status_rb }}</div>
            </div>
        </div>
        <div class="py-1 px-3">
            <StartEndDate :startDate="timeline_rb.start_date" :endDate="timeline_rb.end_date" />
        </div>
    </div>

    <BoxStatsRecurringBills :boxStats="box_stats" />

    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />
</template>