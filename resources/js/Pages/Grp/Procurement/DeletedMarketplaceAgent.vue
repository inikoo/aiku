
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 24 Jun 2023 19:49:19 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Tabs from "@/Components/Navigation/Tabs.vue";
import {computed, defineAsyncComponent, ref} from "vue";
import ModelDetails from "@/Components/ModelDetails.vue";
import {useTabChange} from "@/Composables/tab-change";
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faInventory,
    faWarehouse,
    faPersonDolly,
    faBoxUsd,
    faTruck,
    faTerminal,
    faCameraRetro,faPeopleArrows
} from '@fal';
import TableMarketplaceSuppliers from "@/Components/Tables/Grp/Org/Procurement/TableMarketplaceSuppliers.vue";
import TableMarketplaceSupplierProducts from "@/Components/Tables/Grp/Org/Procurement/TableMarketplaceSupplierProducts.vue";
import MarketplaceAgentShowcase from "@/Components/Showcases/Grp/MarketplaceAgentShowcase.vue";
import { capitalize } from "@/Composables/capitalize"

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))

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
}>()


library.add(
    faInventory,
    faWarehouse,
    faPersonDolly,
    faBoxUsd,
    faTruck,
    faTerminal,
    faCameraRetro,
    faPeopleArrows
);

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        showcase: MarketplaceAgentShowcase,
        suppliers: TableMarketplaceSuppliers,
        supplier_products: TableMarketplaceSupplierProducts,
        details: ModelDetails,
        history: ModelChangelog
    };
    return components[currentTab.value];

});

</script>

<!--suppress HtmlUnknownAttribute -->
<template>
    <!--suppress HtmlRequiredTitleElement -->
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
</template>

