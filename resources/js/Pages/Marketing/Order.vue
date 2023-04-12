
<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 10:36:47 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faBars,
    faDollarSign, faEnvelope, faFileInvoiceDollar,
    faPaperclip, faRoad, faStickyNote, faTag, faThList, faTruck, faUserTag
} from "@/../private/pro-light-svg-icons";
import TablePayments from "@/Pages/Tables/TablePayments.vue";
import TableInvoices from "@/Pages/Tables/TableInvoices.vue";
import TableDeliveryNotes from "@/Pages/Tables/TableDeliveryNotes.vue";
import ModelDetails from "@/Pages/ModelDetails.vue";
import {useTabChange} from "@/Composables/tab-change";
import {computed, defineAsyncComponent, ref} from "vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import PageHeading from '@/Components/Headings/PageHeading.vue';


library.add(
    faBars,
    faThList,
    faUserTag,
    faDollarSign,
    faEnvelope,
    faTag,
    faFileInvoiceDollar,
    faTruck,
    faPaperclip,
    faRoad,
    faStickyNote,
);


const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    invoices: object
    delivery_notes: object
    payments: object
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        payments: TablePayments,
        invoices: TableInvoices,
        delivery_notes: TableDeliveryNotes,
        details: ModelDetails,
        history: ModelChangelog,
    };
    return components[currentTab.value];

});

</script>


<template layout="App">

    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <div class="m-4">
        <DashboardNavigation v-for="(treeMap,idx) in treeMaps" :key="idx" :nodes="treeMap"/>
    </div>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>

</template>

