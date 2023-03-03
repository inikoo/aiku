<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 16:07:20 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { ref } from "vue";

const props = defineProps(["title","pageHead","warehouse","tabs"])

import {library} from '@fortawesome/fontawesome-svg-core';
import {faInventory,faWarehouse,faMapSigns} from '@/../private/pro-light-svg-icons';
import Tabs from "@/Components/Navigation/Tabs.vue";
library.add(faInventory,faWarehouse,faMapSigns);

let currentTab = ref(props.tabs.current)

const handleCustomChange = (tabIndex) => {

    currentTab.value = tabIndex

}

</script>


<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs
        @change:tab="handleCustomChange"
        :tabs="tabs"
        :currentTab="currentTab"
        :handleCustomChange="handleCustomChange"
    >
    </Tabs>

    <div v-show="currentTab === tabIndex" v-for="(tab, tabIndex) in tabs.items" :key="tabIndex">
        {{tab.content}}
    </div>

</template>

