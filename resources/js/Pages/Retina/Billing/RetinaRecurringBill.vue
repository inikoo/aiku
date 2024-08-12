<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { computed, defineAsyncComponent, ref } from 'vue'
import type { Component } from 'vue'

import StartEndDate from '@/Components/Utils/StartEndDate.vue'
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'

// import FileShowcase from '@/xxxxxxxxxxxx'

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs
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
        // showcase: FileShowcase
    }

    return components[currentTab.value]

})

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="grid grid-cols-2">
        <div class="py-4 px-3">
            <div class="flex flex-col justify-center h-full w-full rounded-md px-4 py-2"
                :class="[status_rb === 'current' ? 'bg-green-100 ring-1 ring-green-500 text-green-700' : 'bg-gray-200 ring-1 ring-gray-500 text-gray-700']"
            >
                <div class="text-xs">Status</div>
                <div class="font-semibold capitalize">{{ status_rb }}</div>
            </div>
        </div>
        <div class="py-1 px-3 flex items-center">
            <StartEndDate :startDate="timeline_rb.start_date" :endDate="timeline_rb.end_date" />
        </div>
    </div>

    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />
</template>