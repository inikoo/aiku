<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 16:45:41 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->
<script setup lang="ts">

import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faInventory, faWarehouse, faMapSigns, faChartLine} from '@/../private/pro-light-svg-icons';
import Tabs from "@/Components/Navigation/Tabs.vue";
import {computed, defineAsyncComponent, ref} from "vue";
import WarehouseDashboard from "@/Pages/Inventory/WarehouseDashboard.vue";
import ModelDetails from "@/Pages/ModelDetails.vue";
import TableLocations from "@/Pages/Tables/TableLocations.vue";
import TableHistories from "@/Pages/Tables/TableHistories.vue";
import TableWarehouseAreas from "@/Pages/Tables/TableWarehouseAreas.vue";
import {useTabChange} from "@/Composables/tab-change";

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))
library.add(faInventory, faWarehouse, faMapSigns, faChartLine);

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    },
    warehouse_areas?: object
    locations?: object,
    dashboard?: object,
    history: object

}>()


let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        dashboard: WarehouseDashboard,
        warehouse_areas: TableWarehouseAreas,
        locations: TableLocations,
        details: ModelDetails,
        history: TableHistories
    };
    return components[currentTab.value];

});

</script>


<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>


