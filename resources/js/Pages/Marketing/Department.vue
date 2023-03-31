<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 10:36:47 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faBullhorn,
    faCameraRetro, faClock,
    faCube, faCubes,
    faFolder, faMoneyBillWave, faProjectDiagram, faTags, faUser
} from "@/../private/pro-light-svg-icons";

import PageHeading from '@/Components/Headings/PageHeading.vue';
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Pages/ModelDetails.vue";
import TableCustomers from "@/Pages/Tables/TableCustomers.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableMailshots from "@/Pages/Tables/TableMailshots.vue";
import { faDiagramNext } from "@fortawesome/free-solid-svg-icons";
import TableFamilies from "@/Pages/Tables/TableFamilies.vue";
library.add(
    faFolder,
    faCube,
    faCameraRetro,
    faClock,
    faProjectDiagram,
    faBullhorn,
    faTags,
    faUser,
    faMoneyBillWave,
    faDiagramNext,
    faCubes,
);

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    families: object;
    customers: object;
    mailshots: object;
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        families: TableFamilies,
        mailshots: TableMailshots,
        customers: TableCustomers,
        details: ModelDetails,
        history: ModelChangelog,
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

