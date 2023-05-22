<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 16:07:20 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Tabs from "@/Components/Navigation/Tabs.vue";
import { computed, defineAsyncComponent, ref } from "vue";
import ModelDetails from "@/Pages/ModelDetails.vue";
import { useTabChange } from "@/Composables/tab-change";
import TableSuppliers from "@/Pages/Tables/TableSuppliers.vue";
import TableSupplierProducts from "@/Pages/Tables/TableSupplierProducts.vue";
import AgentShowcase from "@/Pages/Procurement/AgentShowcase.vue";
import TablePurchaseOrders from "@/Pages/Tables/TablePurchaseOrders.vue";

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    },
    showcase?: object
    suppliers?: object
    supplier_products?: object,
    purchase_orders?: object,
    errors?: object
}>()
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faInventory,
    faWarehouse,
    faPersonDolly,
    faParachuteBox,
    faTruck,
    faTerminal,
    faCameraRetro
} from "@/../private/pro-light-svg-icons";
import TablePurchaseOrders from "@/Pages/Tables/TablePurchaseOrders.vue";

library.add(
    faInventory,
    faWarehouse,
    faPersonDolly,
    faParachuteBox,
    faTruck,
    faTerminal,
    faCameraRetro
);

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        showcase: AgentShowcase,
        suppliers: TableSuppliers,
        supplier_products: TableSupplierProducts,
        purchase_orders: TablePurchaseOrders,
        details: ModelDetails,
        history: ModelChangelog,
        purchase_orders: TablePurchaseOrders
    };

    return components[currentTab.value];

});

const getErrors = () => {
    if(props.errors.purchase_order) {
        let confirm = confirm(props.errors.purchase_order);

        if(confirm) {
            const formData = new FormData();
            formData.append("force", true)
        }
    }
}

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <div v-if="errors.purchase_order">{{ getErrors() }}</div>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>

