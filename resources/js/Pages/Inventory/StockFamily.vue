<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 22 Oct 2022 18:57:31 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->



<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';


import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faInventory,
    faBox,
    faDollarSign,
    faPoop,
    faCubes,
    faCube,
    faCameraRetro
} from "@/../private/pro-light-svg-icons";
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import TableProducts from "@/Pages/Tables/TableProducts.vue";
import ModelDetails from "@/Pages/ModelDetails.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableLocations from "@/Pages/Tables/TableLocations.vue";
import TableFamilies from "@/Pages/Tables/TableFamilies.vue";
import { faX } from "@fortawesome/free-solid-svg-icons";
import { capitalize } from "@/Composables/capitalize"

library.add(
    faInventory,
    faBox,
    faDollarSign,
    faPoop,
    faCubes,
    faCube,
    faCameraRetro,
    faX
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
    products: object
    locations: object
    families: object
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        families: TableFamilies,
        products: TableProducts,
        locations: TableLocations,
        details: ModelDetails,
        history: ModelChangelog,
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
