<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

  <script setup  lang="ts">
  import {Head} from '@inertiajs/vue3';
  import PageHeading from '@/Components/Headings/PageHeading.vue';
  import { capitalize } from "@/Composables/capitalize"
  import TableStoredItems from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItems.vue";
  import Tabs from "@/Components/Navigation/Tabs.vue";
  import { computed, ref } from "vue";
  import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue";
  import { useTabChange } from "@/Composables/tab-change";
  import PalletShowcase from "@/Components/Showcases/Org/PalletShowcase.vue";

  const props = defineProps<{
      data: object
      title: string
      pageHead: object
      stored_items?: object
      history?: object
      tabs: object
      showcase : object
  }>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);
const component = computed(() => {
console.log(props.stored_items);
const components = {
    showcase: PalletShowcase,
    stored_items: TableStoredItems,
    history: TableHistories
};
return components[currentTab.value];

});

  </script>

  <template>
      <Head :title="capitalize(title)"/>
      <PageHeading :data="pageHead"></PageHeading>
      <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
     <component :is="component" :data="props[currentTab]"  :tab="currentTab"></component>
  </template>
