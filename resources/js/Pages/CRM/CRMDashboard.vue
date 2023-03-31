<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Fri, 31 Mar 2023 09:40:16 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->



<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faInventory,
    faBox,
    faClock,
    faCameraRetro,
    faPaperclip,
    faCube,
    faCubes,
    faBoxes,
    faHandReceiving, faClipboard, faPoop, faScanner, faDollarSign
} from "../../../private/pro-light-svg-icons";
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Pages/ModelDetails.vue";
import TableProducts from "@/Pages/Tables/TableProducts.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableFamilies from "@/Pages/Tables/TableFamilies.vue";
import TableDepartments from "@/Pages/Tables/TableDepartments.vue";
library.add(
    faInventory,
    faBox,
    faClock,
    faCameraRetro,
    faPaperclip,
    faCube,
    faHandReceiving,
    faClipboard,
    faPoop,
    faScanner,
    faDollarSign,
    faCubes,
    faBoxes,
);


const locale = useLocaleStore();

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    departments: object;
    products: object
    families: object;
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        departments: TableDepartments,
        families: TableFamilies,
        products: TableProducts,
        details: ModelDetails,
        history: ModelChangelog,
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
