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
import Button from '@/Components/Elements/Buttons/Button.vue'

// import FileShowcase from '@/xxxxxxxxxxxx'

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs


}>()

// const currentTab = ref(props.tabs.current)
// const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

// const component = computed(() => {

//     const components: Component = {
//         // showcase: FileShowcase
//     }

//     return components[currentTab.value]

// })

</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <!-- <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" /> -->

    <!-- <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" /> -->
    <div class="px-6">
        <div class="text-xl py-2 w-fit">E-Commerce</div>
        <div class="flex gap-4">
            <div class="bg-gray-50 border border-gray-200 rounded-md w-72 p-4">
                <div class="mb-4 border-b border-gray-300 pb-4 flex gap-x-4 items-center text-xl">
                    <img src="https://cdn-icons-png.flaticon.com/256/5968/5968919.png" alt="" class="h-12">
                    <div class="flex flex-col">
                        <div class="font-semibold">Shopify</div>
                        <div class="text-xs text-gray-500">(Manage product)</div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <a target="_blank" href="http://pupil.aiku.test/authenticate?shop=aikuu">
                        <Button label="Connect" type="black" />
                    </a>
                </div>
            </div>
            
            <div class="border border-dashed  border-gray-300 rounded-md w-72 p-4 flex items-center justify-center text-gray-400 italic">
                Coming soon
            </div>
        </div>
    </div>
</template>