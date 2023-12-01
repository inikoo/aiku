<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 16 Aug 2023 20:30:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
  faAnalytics, faBrowser,
  faChartLine, faDraftingCompass, faRoad, faSlidersH, faUsersClass, faClock
} from '@fal/';

import PageHeading from "@/Components/Headings/PageHeading.vue";
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Components/ModelDetails.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableWebpages from "@/Components/Tables/TableWebpages.vue";
import { capitalize } from "@/Composables/capitalize";
import TableHistories from "@/Components/Tables/TableHistories.vue";

library.add(
  faChartLine,
  faClock,
  faAnalytics,
  faUsersClass,
  faDraftingCompass,
  faSlidersH,
  faRoad,
  faBrowser
);


const props = defineProps<{
  title: string,
  pageHead: object,
  tabs: {
    current: string;
    navigation: object;
  }
  webpages?: string;
  changelog?: object
}>();

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

  const components = {
    webpages: TableWebpages,
    details: ModelDetails,
    changelog: TableHistories
  };
  return components[currentTab.value];

});

</script>


<template layout="App">
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead"></PageHeading>
  <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
  <component :is="component" :data="props[currentTab]"></component>
</template>

