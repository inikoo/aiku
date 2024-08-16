<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 13 Oct 2022 15:35:22 Central European Summer Plane Malaga - East Midlands UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
    faCube,
    faFileInvoice,
    faFolder,
    faFolderTree,
    faChartLine,
    faShoppingCart, faStickyNote
} from '@fal';
import { faCheckCircle } from '@fas';

import PageHeading from "@/Components/Headings/PageHeading.vue";
import { capitalize } from "@/Composables/capitalize";
import Tabs from "@/Components/Navigation/Tabs.vue";
import { computed, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";

import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue";
import ShopShowcase from "@/Components/Showcases/Grp/ShopShowcase.vue";

library.add(faChartLine, faCheckCircle, faFolderTree, faFolder, faCube, faShoppingCart, faFileInvoice, faStickyNote);

const props = defineProps<{
    pageHead: object
    tabs: {
        current: string;
        navigation: object;
    },
    title: string
    showcase?: object
     history?: {}

}>();

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        showcase: ShopShowcase,
        history: TableHistories,
    };
    return components[currentTab.value];

});

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :tab="currentTab" :data="props[currentTab]"></component>
</template>

