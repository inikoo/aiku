<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Jan 2024 15:13:18 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import TablePalletReturns from "@/Components/Tables/Grp/Org/Fulfilment/TablePalletReturns.vue"
import TablePalletReturnItemUploads from "@/Components/Tables/TablePalletReturnItemUploads.vue"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref } from "vue"
import type { Component } from "vue"
import type { Navigation } from "@/types/Tabs"
import { useTabChange } from "@/Composables/tab-change"

const props = defineProps<{
    title: string
    pageHead: TSPageHeading
    data: {}
    tabs: {
        current: string
        navigation: Navigation
    }
    returns?: {}
    uploads?: {}
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        returns: TablePalletReturns,
        uploads: TablePalletReturnItemUploads
    }

    return components[currentTab.value]
})

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :tab="currentTab" :data="props[currentTab]"></component>
</template>
