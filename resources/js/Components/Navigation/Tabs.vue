<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 02 Mar 2023 21:32:55 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
defineProps(['tabs']);
defineEmits(['tab:clicked']);

import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

</script>

<template>
    <div class="mb-5">
        <div class="sm:hidden">
            <label for="tabs" class="sr-only">Select a tab</label>
            <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
            <select id="tabs" name="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option v-for="(tab, tabIndex) in tabs.items" :key="tabIndex" :selected="tabs.current">{{ tab.name }}</option>
            </select>
        </div>
        <div class="hidden sm:block">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 ml-4" aria-label="Tabs">
                    <button
                        @click="$emit('tabChanged')"
                        v-for="(tab, tabIndex) in tabs.items" :key="tabIndex"
                          :class="[tabIndex===tabs.current ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm']"
                          :aria-current="tab.current ? 'page' : undefined">
                        <font-awesome-icon :icon="tab.icon" :class="[tab.current ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500', '-ml-0.5 mr-2 h-5 w-5']" aria-hidden="true"/>
                        <span>{{ tab.name }}</span>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>
