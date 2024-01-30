<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

  <script setup  lang="ts">
 import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import TableCustomers from "@/Components/Tables/TableCustomers.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import {computed, ref} from "vue";
import {useTabChange} from "@/Composables/tab-change";
import TableHistories from "@/Components/Tables/TableHistories.vue";
  
  const props = defineProps<{
    title: string
    tabs: object
    showcase?:object
    history?:object
    pageHead: object
}>()



let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        showcase: null,
        history: TableHistories
    };
    return components[currentTab.value];

});
  </script>
  
  <template layout="App">
     <div>hallo</div>
  </template>
  