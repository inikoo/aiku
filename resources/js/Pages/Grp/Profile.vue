<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { computed, defineAsyncComponent, ref } from 'vue'
import type { Component } from 'vue'
import ModelDetails from "@/Components/ModelDetails.vue";

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'

import TableHistories from "@/Components/Tables/TableHistories.vue";
import TableTimesheets from "@/Components/Tables/Grp/Org/HumanResources/TableTimesheets.vue";
import TableUserRequestLogs from "@/Components/Tables/Grp/SysAdmin/TableUserRequestLogs.vue"

// import FileShowcase from '@/xxxxxxxxxxxx'

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs,
    history?: {}
    timesheets?:{}
    visit_logs?:{}
    // today_timesheets?:object

    
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
console.log(props.visit_logs)
const component = computed(() => {

    const components: Component = {
        // showcase: FileShowcase
        showcase: ModelDetails,
        visit_logs: TableHistories,
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