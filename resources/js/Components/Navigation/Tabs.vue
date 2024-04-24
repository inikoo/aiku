<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 18 Mar 2023 04:04:35 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { inject, ref } from "vue"
import { capitalize } from "@/Composables/capitalize"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faInfoCircle, faPallet } from '@fas'
import { faRoad, faClock, faDatabase, faNetworkWired } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { layoutStructure } from "@/Composables/useLayoutStructure"

library.add(faInfoCircle, faRoad, faClock, faDatabase, faPallet, faNetworkWired)

const layoutStore = inject('layout', layoutStructure)

const props = defineProps<{
    navigation: {
        tab: {
            align: string
            type: string
            icon: string
            title: string
            iconClass: string
        }
    },
    current: string
}>()

const emits = defineEmits<{
    (e: 'update:tab', value: string): void
}>()

const currentTab = ref(props.current)
const changeTab = function (tabSlug: string) {
    currentTab.value = tabSlug
}


const tabIconClass = function (current, type, align, extraClass) {
    let iconClass = '-ml-0.5 h-5 w-5   ' + extraClass
    // iconClass += current ? 'text-indigo-500 ' : 'text-gray-400 group-hover:text-gray-500 ';
    iconClass += (type == 'icon' && align == 'right') ? 'ml-2 ' : 'mr-2 '
    return iconClass
}

</script>

<template>
    <div>
        <!-- Tabs: Mobile view -->
        <div class="sm:hidden px-3 pt-2">
            <label for="tabs" class="sr-only">Select a tab</label>
            <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
            <select id="tabs" name="tabs" class="block w-full capitalize rounded-md border-gray-300 focus:border-gray-500 focus:ring-gray-500"
                @input="(val: any) => { emits('update:tab', val.target.value), changeTab(val.target.value) }"
            >
                <option v-for="(tab, tabSlug) in navigation" :key="tabSlug" :selected="tabSlug == currentTab" :value="tabSlug" class="capitalize">{{ tab.title }}</option>
            </select>
        </div>

        <!-- Tabs: Desktop view -->
        <div class="hidden sm:block">
            <div class="border-b border-gray-200 flex">

                <!-- Left section -->
                <nav class="-mb-px flex w-full gap-x-6 ml-4" aria-label="Tabs">
                    <template v-for="(tab, tabSlug) in navigation" :key="tabSlug">
                        <button
                            v-if="tab.align !== 'right'"
                            @click="[emits('update:tab', tabSlug),changeTab(tabSlug)]"
                            :class="[tabSlug === currentTab ? 'tabNavigationActive' : 'tabNavigation']"
                            class="group flex items-center py-2 px-1 font-medium capitalize text-left text-sm md:text-base w-fit"
                            :aria-current="tabSlug === currentTab ? 'page' : undefined">
                            <FontAwesomeIcon v-if="tab.icon" :icon="tab.icon" :class="tabIconClass(tabSlug === currentTab, tab.type, tab.align, tab.iconClass || '')" aria-hidden="true"/>
                            {{ tab.title }}
                        </button>
                    </template>
                </nav>

                <!-- Right section -->
                <nav class="flex flex-row-reverse mr-4" aria-label="Secondary Tabs">
                    <template v-for="(tab,tabSlug) in navigation" :key="tabSlug">
                        <button
                            v-if="tab.align === 'right'"
                            @click="[emits('update:tab', tabSlug), changeTab(tabSlug)]"
                            :class="[tabSlug === currentTab ? 'tabNavigationActive' : 'tabNavigation',
                                'group inline-flex justify-center items-center py-2 px-2 border-b-2 font-medium text-sm']"
                            :aria-current="tabSlug === currentTab ? 'page' : undefined"
                            v-tooltip="capitalize(tab.title)"
                        >
                            <FontAwesomeIcon v-if="tab.icon" :icon="tab.icon" class="h-5 w-5" aria-hidden="true"/>
                            <span v-if="tab.type!=='icon'" class="capitalize">{{ tab.title }}</span>
                        </button>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.tabNavigation {
    @apply transition-all duration-75;
    filter: saturate(0);
    border-bottom: v-bind('`2px solid transparent`');
    color: v-bind('`${layoutStore.app.theme[0]}99`');
    
    &:hover {
        filter: saturate(0.85);
        border-bottom: v-bind('`2px solid ${layoutStore.app.theme[0]}AA`');
        color: v-bind('`${layoutStore.app.theme[0]}AA`');
    }
}

.tabNavigationActive {
    border-bottom: v-bind('`2px solid ${layoutStore.app.theme[0]}`');
    color: v-bind('layoutStore.app.theme[0]');
}

</style>