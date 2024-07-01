<script setup lang='ts'>
import { capitalize } from "@/Composables/capitalize"
import { routeType } from '@/types/route'
import MetaLabel from "@/Components/Headings/MetaLabel.vue"
import { Link } from "@inertiajs/vue3"
import { inject } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faNarwhal } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faNarwhal)

const layout = inject('layout', layoutStructure)

const props = defineProps<{
    dataNavigation: {
        leftIcon: {
            icon: string | string[]
            tooltip: string
        }
        href: routeType
        label: string
        number: string
    }[]
}>()

// const originUrl = location.origin
</script>

<template>
    <div class="relative select-none w-full flex flex-wrap px-4 sm:mt-0 sm:mb-1 border-gray-300 sm:gap-y-1 items-end text-gray-400 text-xs">
        <!-- Tab: Home/dashboard -->
        <div v-if="dataNavigation.length"
            class="py-1 flex items-center transition-all"
            :class="[
                layout.currentRoute === dataNavigation[0]?.href?.name ? 'text-indigo-500 px-2 bg-white rounded-t-md rounded-tl-none sm:border sm:border-transparent sm:border-r-gray-300' : 'tabSubNav -ml-2 md:ml-0'
            ]"
        >
            <component :is="dataNavigation[0].href?.name ? Link : 'div'" 
                class="flex items-center py-1.5 px-3 rounded transition-all"
                :href="dataNavigation[0].href.name ? route(dataNavigation[0].href.name, dataNavigation[0].href.parameters) : '#'"
                :class="[
                    layout.currentRoute === dataNavigation[0].href.name ? `` : `bg-gray-100 hover:bg-gray-200 text-gray-600`
                ]"
                :style="{
                    backgroundColor: layout.currentRoute === dataNavigation[0].href.name ? layout?.app?.theme[4] + '22' : '',
                    color: layout.currentRoute === dataNavigation[0].href.name ? `color-mix(in srgb, ${layout?.app?.theme[4]} 50%, black)` : ''
                }"
            >
                <FontAwesomeIcon v-if="dataNavigation[0].leftIcon" :icon="dataNavigation[0].leftIcon.icon" v-tooltip="capitalize(dataNavigation[0].leftIcon.tooltip)" aria-hidden="true" class="pr-1" />
                <MetaLabel :item="dataNavigation[0]" />
            </component>
        </div>

        <!-- Tabs -->
        <TransitionGroup>
            <component
                v-for="subNav, itemIdx in [...dataNavigation].slice(1)"
                :key="'subNav' + itemIdx"
                :is="subNav.href?.name ? Link : 'div'"
                :href="subNav.href?.name ? route(subNav.href.name, subNav.href.parameters) : '#'"
                class="py-1.5 flex items-center transition-all"
                :class="[
                    layout.currentRoute.includes(subNav.href?.name) ? `tabSubNavActive` : `tabSubNav`
                ]"
            >
                <div class="py-1 px-1.5 flex items-center">
                    <FontAwesomeIcon v-if="subNav.leftIcon" :icon="subNav.leftIcon.icon" v-tooltip="capitalize(subNav.leftIcon.tooltip)" aria-hidden="true" class="pr-1" />
                    <MetaLabel :item="subNav" />
                </div>
            </component>
        </TransitionGroup>

        <div class="hidden border-b border-gray-300 px-1 sm:flex flex-auto">&nbsp</div>

    </div>
</template>

<style lang="scss" scoped>
.tabSubNavActive {
    @apply px-2 bg-white border sm:border-b-transparent rounded-md sm:rounded-b-none sm:rounded-t-md border-gray-300;

    color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 40%, black)`') !important;
}

.tabSubNav {
    @apply px-2 sm:border border-transparent border-b-gray-300;

    color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 80%, black)`') !important;

    &:hover {
        color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 60%, black)`') !important;
    }
}
</style>