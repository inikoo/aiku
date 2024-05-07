<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 14 Sept 2022 02:15:21 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import PageHeading from '@/Components/Headings/PageHeading.vue';
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Components/ModelDetails.vue";
import TableUserRequestLogs from "@/Components/Tables/TableUserRequestLogs.vue";
import TableHistories from "@/Components/Tables/TableHistories.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import { faIdCard, faUser, faClock, faDatabase, faEnvelope, faHexagon, faFile } from '@fal';
import { library } from "@fortawesome/fontawesome-svg-core";
import { capitalize } from "@/Composables/capitalize"
import {faRoad} from "@fas";

library.add(
    faIdCard,
    faUser,
    faClock,
    faDatabase,
    faEnvelope,
    faHexagon,
    faFile,
    faRoad

)

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    },
    request_logs: object;
    history: object;

}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        details: ModelDetails,
        request_logs: TableUserRequestLogs,
        history: TableHistories
    };
    return components[currentTab.value];

});
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
</template>
