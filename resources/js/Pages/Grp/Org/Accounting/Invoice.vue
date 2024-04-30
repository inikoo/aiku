<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 10:36:47 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faChartLine, faCreditCard, faCube, faFolder, faPercent } from '@fal'

import PageHeading from '@/Components/Headings/PageHeading.vue'
import { computed, defineAsyncComponent, ref } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import ModelDetails from "@/Components/ModelDetails.vue"
import TablePayments from "@/Components/Tables/Grp/Accounting/TablePayments.vue"
import OperationsInvoiceShowcase from "@/Components/Showcases/Grp/Fulfilment/OperationsInvoiceShowcase.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { faClock, faFileInvoice } from '@fas'
import { capitalize } from "@/Composables/capitalize"

library.add(faFolder, faCube, faChartLine, faCreditCard, faClock, faFileInvoice, faPercent)

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: {}
    tabs: {
        current: string
        navigation: {}
    }
    showcase: {}
    payments: {}
    details: {}
    history: {}
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components = {
        showcase: OperationsInvoiceShowcase,
        payments: TablePayments,
        details: ModelDetails,
        history: ModelChangelog,
    }

    return components[currentTab.value]
})

</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab" />
</template>
