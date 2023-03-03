<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 02 Mar 2023 21:32:55 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>

import { ref } from "vue";
import Tabs from "@/Components/Navigation/Tabs.vue";

const props = defineProps(["tabs"])



let currentTab = ref(props.tabs.current)

const handleCustomChange = (tabIndex) => {

    currentTab.value = tabIndex

}


</script>

<template>
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
