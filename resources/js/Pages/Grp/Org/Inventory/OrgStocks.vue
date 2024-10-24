<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 22 Oct 2022 18:55:18 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import TableOrgStocks from "@/Components/Tables/Grp/Org/Inventory/TableOrgStocks.vue";
import { capitalize } from "@/Composables/capitalize";
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading";
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import type { Component } from 'vue'
import TableOrgStockFamilies from "@/Components/Tables/Grp/Org/Inventory/TableOrgStockFamilies.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";

const props = defineProps<{
  data: object
  title: string
  pageHead: PageHeadingTypes
  tabs: {
        current: string
        navigation: {}
    }
  org_stocks: {}
  org_stock_families: {}
}>();

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: {[key: string]: Component} = {
      org_stocks: TableOrgStocks,
      org_stock_families: TableOrgStockFamilies,
    }

    return components[currentTab.value]
})
console.log(props.org_stocks)
</script>

<template>
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead"></PageHeading>
  <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
  <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
</template>

