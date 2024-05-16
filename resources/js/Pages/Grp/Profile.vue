<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { computed, ref } from 'vue'
import type { Component } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'

import TableHistories from "@/Components/Tables/TableHistories.vue"
import TableTimesheets from "@/Components/Tables/Grp/Org/HumanResources/TableTimesheets.vue"
import TableUserRequestLogs from "@/Components/Tables/Grp/SysAdmin/TableUserRequestLogs.vue"
import ProfileShowcase from "@/Components/Profile/ProfileShowcase.vue"


const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs,
    history?: {}
    timesheets?: {}
    visit_logs?: {}
    showcase: {}

}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        showcase: ProfileShowcase,
        history: TableHistories,
        timesheets: TableTimesheets,
        visit_logs: TableUserRequestLogs,
        // today_timesheets: TableTimesheets,
    }

    return components[currentTab.value]

})

</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />

    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />
</template>