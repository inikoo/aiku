<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import TableProspects from "@/Components/Tables/Grp/Org/CRM/TableProspects.vue";
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { ref, computed } from 'vue'
import TableMailshots from "@/Components/Tables/TableMailshots.vue";
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from '@/Components/Tables/Grp/Helpers/TableHistories.vue'
import ProspectsDashboard from '@/Pages/Grp/Org/Shop/CRM/ProspectsDashboard.vue'

import { PageHeading as PageHeadingTypes } from "@/types/PageHeading";

const props = defineProps<{
  title: string
      pageHead: PageHeadingTypes
      tabs: {
          current: string
          navigation: {}
      }
      dashboard : {}
      history : {}
      lists : {}
      prospects : {}
      tagsList : {}
      tagRoute : {}
}>()

console.log(props)

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
      const components: {[key: string]: Component} = {
        dashboard: ProspectsDashboard,
        prospects: TableProspects,
        mailshots: TableMailshots,
        history: TableHistories,
       /*  lists: TableProspectLists */
      }
  
      return components[currentTab.value]
  })

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
</template>

