<script setup lang='ts'>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { capitalize } from "@/Composables/capitalize"
import { routeType } from '@/types/route'
import MetaLabel from "@/Components/Headings/MetaLabel.vue"
import { Link } from "@inertiajs/vue3"
import { inject } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"

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
    <!-- <div class="flex flex-col sm:flex-row"> -->
        <div class="select-none w-full flex flex-col sm:mt-0 sm:flex-row mb-1 border-gray-300 gap-y-1 items-end text-gray-400 text-xs">
            <!-- Tab: Home/dashboard -->
            <div 
                class="py-1 px-1 flex items-center"
                :class="[
                    layout.currentRoute === dataNavigation[0].href.name ? 'text-indigo-500 px-1 bg-white border-x border-t rounded-t-md border-gray-300' : 'border-b border-gray-300'
                ]"
            >
                <component :is="dataNavigation[0].href?.name ? Link : 'div'"  class="flex items-center py-1.5 px-3 rounded"
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
            <component :is="subNav.href?.name ? Link : 'div'" v-for="subNav, itemIdx in [...dataNavigation].slice(1)"
                :href="subNav.href?.name ? route(subNav.href.name, subNav.href.parameters) : '#'"
                class="py-1.5 flex items-center"
                :class="[
                    layout.currentRoute.includes(subNav.href?.name) ? `tabSubNavActive` : `tabSubNav`
                ]"
            >
                <div class="py-1 px-1.5 flex items-center">
                    <FontAwesomeIcon v-if="subNav.leftIcon" :icon="subNav.leftIcon.icon" v-tooltip="capitalize(subNav.leftIcon.tooltip)" aria-hidden="true" class="pr-1" />
                    <MetaLabel :item="subNav" />
                </div>
            </component>

            <!-- <div class="border-b border-gray-300 px-1"></div> -->

        </div>
    <!-- </div> -->
</template>

<style lang="scss" scoped>
.tabSubNavActive {
    @apply px-1 bg-white border-x border-t rounded-t-md border-gray-300;

    color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 40%, black)`') !important;
}

.tabSubNav {
    @apply px-2 border-b border-gray-300;

    color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 80%, black)`') !important;

    &:hover {
        color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 60%, black)`') !important;
    }
}
</style>