<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Apr 2024 18:08:36 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableRecurringBills from "@/Components/Tables/Grp/Org/Fulfilment/TableRecurringBills.vue";
import {capitalize} from "@/Composables/capitalize"
import {computed, ref} from "vue";
import {useTabChange} from "@/Composables/tab-change";
import TableHistories from "@/Components/Tables/TableHistories.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";

const props = defineProps<{
  pageHead: object
  title: string
  tabs: {
    current: string
    navigation: {}
  }
  recurring_bills?: {}
  history?: {}
}>()

let currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab)

const component = computed(() => {

  const components = {
    recurring_bills: TableRecurringBills,
    history: TableHistories
  }
  return components[currentTab.value]

});

</script>

<template>
  <Head :title="capitalize(title)"/>
  <PageHeading :data="pageHead"></PageHeading>
  <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
  <component :is="component" :tab="currentTab" :data="props[currentTab]"></component>
</template>

