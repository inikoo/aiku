<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 18 Mar 2023 04:04:35 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {ref} from "vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const props = defineProps<{
    navigation: object,
    current: string
}>()

defineEmits(['update:tab']);

let currentTab = ref(props.current);


const changeTab = function (tabSlug) {
    currentTab.value = tabSlug;
}

</script>

<template>
    <div class="mb-5">
        <div class="sm:hidden">
            <label for="tabs" class="sr-only">Select a tab</label>
            <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
            <select id="tabs" name="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option v-for="(tab,tabSlug) in navigation" :key="tabSlug" :selected="currentTab">{{ tab.title }}</option>
            </select>
        </div>
        <div class="hidden sm:block">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 ml-4" aria-label="Tabs">
                    <button
                        v-for="(tab,tabSlug) in navigation"
                        :key="tabSlug"
                        @click="[$emit('update:tab', tabSlug),changeTab(tabSlug)]"
                        :class="[tabSlug===currentTab ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm']"
                        :aria-current="tabSlug===currentTab ? 'page' : undefined">
                        <FontAwesomeIcon v-if="tab.icon" :icon="tab.icon" :class="[tabSlug===currentTab ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500', '-ml-0.5 mr-2 h-5 w-5']" aria-hidden="true"/>
                        <span class="capitalize">{{ tab.title }}</span>
                    </button>
                </nav>
            </div>
        </div>
    </div>


</template>
