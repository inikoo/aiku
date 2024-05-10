<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 12 Oct 2022 16:50:56 Central European Summer Time, Benalmádena, Malaga,Spain
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableShops from "@/Components/Tables/TableShops.vue";
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue";
import { computed, ref } from "vue";
import { library } from "@fortawesome/fontawesome-svg-core";

import TableDepartments from "@/Components/Tables/Grp/Org/Market/TableDepartments.vue";
import TableFamilies from "@/Components/Tables/TableFamilies.vue";
import TableProducts from "@/Components/Tables/TableProducts.vue";
import { useTabChange } from "@/Composables/tab-change";
import {
    faCube,faFolder,faFolderTree
} from '@fal';
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading";

library.add(
    faCube,faFolder,faFolderTree
);

const props = defineProps <{
    pageHead: PageHeadingTypes
    tabs: {
        current: string;
        navigation: object;
    },
    title: string
    shops?: object
    departments?: object
    families?: object
    products?: object

}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        shops: TableShops,
        departments: TableDepartments,
        families: TableFamilies,
        products: TableProducts,
    };
    return components[currentTab.value];

});

</script>

<template>
    <!--suppress HtmlRequiredTitleElement -->
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']"  @update:tab="handleTabUpdate"/>
    <component :is="component" :tab="currentTab"  :data="props[currentTab]"></component>
</template>

