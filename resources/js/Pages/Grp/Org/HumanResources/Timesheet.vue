<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 08 Sept 2022 00:38:38 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { capitalize } from "@/Composables/capitalize";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { computed, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import Tabs from "@/Components/Navigation/Tabs.vue";
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading";
import type { Navigation } from "@/types/Tabs";
import TableTimeTrackers from "@/Components/Tables/Grp/Org/HumanResources/TableTimeTrackers.vue";
import TableClockings from "@/Components/Tables/Grp/Org/HumanResources/TableClockings.vue";
import TableHistories from "@/Components/Tables/TableHistories.vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import {faVoteYea,faArrowsH} from '@fal';

library.add(
 faVoteYea,faArrowsH
)

const props = defineProps<{
  title: string,
  pageHead: PageHeadingTypes,
  tabs: {
    current: string;
    navigation: Navigation;
  },
  history?: object,
  time_trackers?: object,
  clockings?: object,

}>();

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

  const components = {
    time_trackers: TableTimeTrackers,
    clockings: TableClockings,
    history: TableHistories
  };
  return components[currentTab.value];

});


</script>


<template>
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead"></PageHeading>
  <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
  <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
</template>

