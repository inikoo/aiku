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
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faInventory,
    faWarehouse,
    faPersonDolly,
    faParachuteBox,
    faTruck,
    faTerminal,
    faCameraRetro,faPeopleArrows
} from "@/../private/pro-light-svg-icons";
import TableMarketplaceSuppliers from "@/Pages/Tables/TableMarketplaceSuppliers.vue";
import TableMarketplaceSupplierProducts from "@/Pages/Tables/TableMarketplaceSupplierProducts.vue";

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    },
    suppliers?: object
    supplier_products?: object,
}>()


library.add(
    faInventory,
    faWarehouse,
    faPersonDolly,
    faParachuteBox,
    faTruck,
    faTerminal,
    faCameraRetro,
    faPeopleArrows
);

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        suppliers: TableMarketplaceSuppliers,
        supplier_products: TableMarketplaceSupplierProducts,
        details: ModelDetails,
        history: ModelChangelog
    };
    return components[currentTab.value];

});

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>

