
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 10 May 2024 17:32:47 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faChartNetwork } from '@fal'
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, defineAsyncComponent, ref } from "vue"
import WarehouseDashboard from "@/Components/Dashboards/WarehouseDashboard.vue"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import ModelDetails from "@/Components/ModelDetails.vue";

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))
library.add(faChartNetwork)

const props = defineProps<{
    pageHead: {}
    tabs: {
        current: string
        navigation: {}
    }
    tagsList: {
        data: {}[]
    }
    title: string
    dashboard?: {}
    history?: {}
}>()


let currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab)

const component = computed(() => {

    const components = {
        dashboard: WarehouseDashboard,
        details: ModelDetails,
        history: TableHistories
    }
    return components[currentTab.value]

});

</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :tab="currentTab" :data="props[currentTab]" :tagsList="tagsList.data"></component>
</template>
