<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3"
import { ref } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import DashboardSettings from "@/Components/DataDisplay/Dashboard/DashboardSettings.vue"
import AcquisitionTab from "@/Components/Marketing/V2/AcquisitionTab.vue"
import OrdersTab from "@/Components/Marketing/V2/OrdersTab.vue"
import JourneyTab from "@/Components/Marketing/V2/JourneyTab.vue"
import ComparePeriod, { CompareProp } from "@/Components/Marketing/V2/ComparePeriod.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"
import { Tabs as TSTabs } from "@/types/Tabs"
import { Intervals, Settings } from "@/types/Components/Dashboard"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBullhorn, faUserPlus, faShoppingCart, faRoute } from "@fal"

library.add(faBullhorn, faUserPlus, faShoppingCart, faRoute)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    tabs: TSTabs
    intervals: Intervals
    settings: Settings
    compare: CompareProp
    acquisition?: InstanceType<typeof AcquisitionTab>["$props"]["data"]
    orders?: InstanceType<typeof OrdersTab>["$props"]["data"]
    journey?: InstanceType<typeof JourneyTab>["$props"]["data"]
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => {
    if (tabSlug === currentTab.value) {
        return
    }

    const url = new URL(window.location.href)
    const target = new URLSearchParams({ tab: tabSlug })
    ;["compare", "compare_from", "compare_to"].forEach(key => {
        const value = url.searchParams.get(key)
        if (value) {
            target.set(key, value)
        }
    })

    router.get(`${url.pathname}?${target.toString()}`, {}, {
        only: [tabSlug],
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { currentTab.value = tabSlug },
    })
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <div class="pt-3">
        <DashboardSettings
            :intervals="intervals"
            :settings="settings"
            :currentTab="currentTab"
            :reloadOnly="['intervals', 'compare', currentTab]"
        >
            <template #actions>
                <ComparePeriod v-if="currentTab !== 'journey'" :compare="compare" :currentTab="currentTab" />
            </template>
        </DashboardSettings>
    </div>
    <AcquisitionTab v-if="currentTab === 'acquisition' && acquisition" :key="`${acquisition.from}-${acquisition.to}`" :data="acquisition" />
    <OrdersTab v-if="currentTab === 'orders' && orders" :data="orders" />
    <JourneyTab v-if="currentTab === 'journey' && journey" :data="journey" />
</template>
