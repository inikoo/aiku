

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 06 Apr 2023 15:16:45 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faCube,faFolder,faFolders,faChartLine} from "@/../private/pro-light-svg-icons";
import { computed, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import TableProducts from "@/Pages/Tables/TableProducts.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableFamilies from "@/Pages/Tables/TableFamilies.vue";
import TableDepartments from "@/Pages/Tables/TableDepartments.vue";
import { capitalize } from "@/Composables/capitalize"
library.add(faCube,faFolder,faFolders,faChartLine);


const locale = useLocaleStore();


const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object
    }
    departments?: object
    products?: object
    families?: object,
}>()
let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        departments: TableDepartments,
        families: TableFamilies,
        products: TableProducts,
    };
    return components[currentTab.value];

});

</script>

<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]" :tab="currentTab"  ></component>
</template>
