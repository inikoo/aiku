<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";

import { useTabChange } from "@/Composables/tab-change";
import { capitalize } from "@/Composables/capitalize";
import { computed, ref } from "vue";
import type { Component } from "vue";

import { PageHeading as TSPageHeading } from "@/types/PageHeading";
import { Tabs as TSTabs } from "@/types/Tabs";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faInboxOut, faSortAlt, faProjectDiagram, faPhoneVolume } from "@fal";

library.add(faInboxOut, faSortAlt, faProjectDiagram, faPhoneVolume);

const props = defineProps<{
  title: string,
  pageHead: TSPageHeading
  tabs: TSTabs


}>();

const currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

  const components: Component = {
    dashboard: {}
  };

  return components[currentTab.value];

});

</script>


<template>
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead" />
  <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
  <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />
</template>