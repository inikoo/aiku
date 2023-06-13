<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 16:07:20 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Tabs from "@/Components/Navigation/Tabs.vue";
import {computed, defineAsyncComponent, ref} from "vue";
import ModelDetails from "@/Pages/ModelDetails.vue";
import {useTabChange} from "@/Composables/tab-change";
import TablePurchaseOrderItems from "@/Pages/Tables/TablePurchaseOrderItems.vue";
import { capitalize } from "@/Composables/capitalize"

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    },
    showcase: object,
    items: object,
    history: object
}>()
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faInventory,
    faWarehouse,
    faPersonDolly,
    faBoxUsd,
    faTruck,
    faTerminal,
    faInfoCircle,
    faCameraRetro
} from "@/../private/pro-light-svg-icons";
import TableHistories from "@/Pages/Tables/TableHistories.vue";
// import TablePurchaseOrders from "@/Pages/Tables/TablePurchaseOrders.vue";

library.add(
    faInventory,
    faWarehouse,
    faPersonDolly,
    faBoxUsd,
    faTruck,
    faTerminal,
    faInfoCircle,
    faCameraRetro
);

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        showcase: ModelDetails,
        history: TableHistories,
        items: TablePurchaseOrderItems
    };

    return components[currentTab.value];

});

</script>

<template layout="App">
    <Head :title="capitalize(title)" />
    <!-- {{ showcase }} -->
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>
