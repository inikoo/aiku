<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 16 Sept 2022 12:56:59 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';


import {library} from '@fortawesome/fontawesome-svg-core';
import { faInventory, faWarehouse, faMapSigns, faChartLine, faClock } from "@/../private/pro-light-svg-icons";
import Tabs from "@/Components/Navigation/Tabs.vue";
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Pages/ModelDetails.vue";
import TableLocations from "@/Pages/Tables/TableLocations.vue";
import TableHistories from "@/Pages/Tables/TableHistories.vue";

import { capitalize } from "@/Composables/capitalize"
library.add(
    faInventory,
    faWarehouse,
    faMapSigns,
    faChartLine,
    faClock,
);

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    locations?: object;
    history?: object;
}>()
let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        locations: TableLocations,
        details: ModelDetails,
        history: TableHistories,
    };
    return components[currentTab.value];

});

</script>

<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>

