<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 16:07:20 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Tabs from "@/Components/Navigation/Tabs.vue";
import {computed, defineAsyncComponent, ref} from "vue";
import ModelDetails from "@/Pages/ModelDetails.vue";
import {useTabChange} from "@/Composables/tab-change";

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
        showcase?: object
        supplier_products?: object,
        purchase_orders?: object,
        errors?: object,
        history: object

    },
}>()
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faInventory,
    faWarehouse,
    faPersonDolly,
    faBoxUsd,
    faTruck,
    faTerminal,
    faCameraRetro, faPaperclip, faPoop, faMoneyBill, faClipboard
} from '@fal/';
import AgentShowcase from "@/Pages/Procurement/AgentShowcase.vue";
import TableSupplierProducts from "@/Pages/Tables/TableSupplierProducts.vue";
import TablePurchaseOrders from "@/Pages/Tables/TablePurchaseOrders.vue";
import { capitalize } from "@/Composables/capitalize"
import TableHistories from "@/Pages/Tables/TableHistories.vue";

library.add(
    faInventory,
    faWarehouse,
    faPersonDolly,
    faBoxUsd,
    faTruck,
    faTerminal,
    faCameraRetro,
    faPaperclip,
    faPoop,
    faMoneyBill,
    faClipboard,
);

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        showcase: AgentShowcase,
        supplier_products: TableSupplierProducts,
        purchase_orders: TablePurchaseOrders,
        details: ModelDetails,
        history: TableHistories
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

