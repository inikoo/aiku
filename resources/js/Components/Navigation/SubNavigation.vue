<script setup lang='ts'>
import { capitalize } from "@/Composables/capitalize"
import { routeType } from '@/types/route'
import MetaLabel from "@/Components/Headings/MetaLabel.vue"
import { Link } from "@inertiajs/vue3"
import { inject, ref } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faNarwhal } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useTruncate } from '@/Composables/useTruncate'
library.add(faNarwhal)

const layout = inject('layout', layoutStructure)

const props = defineProps<{
    dataNavigation: {
        leftIcon: {
            icon: string | string[]
            tooltip: string
        }
        align: 'left' | 'right'
        root?: string
        href: routeType
        label: string
        number: string
    }[]
}>()

const isLoading = ref<string | boolean | number>(false)
const locale = inject('locale', aikuLocaleStructure)

// const originUrl = location.origin
</script>

<template>
    <div class="relative select-none w-full flex px-4 sm:mt-1 lg:mt-0 sm:mb-1 border-b border-gray-300 sm:gap-y-1 items-end text-gray-400 text-xs">
        <!-- Tab: Home/dashboard -->
        <!-- <div v-if="dataNavigation.length && false"
            class="py-1 flex items-center transition-all"
            :class="[
                layout.currentRoute === dataNavigation[0]?.href?.name ? 'text-indigo-500 px-2 bg-white rounded-t-md rounded-tl-none sm:border sm:border-transparent sm:border-r-gray-300' : 'tabSubNav -ml-2 md:ml-0'
            ]"
        >
            <component :is="dataNavigation[0].href?.name ? Link : 'div'" 
                class="flex items-center py-1.5 px-3 rounded transition-all"
                :href="dataNavigation[0].href.name ? route(dataNavigation[0].href.name, dataNavigation[0].href.parameters) : '#'"
                @start="() => isLoading = 'home'"
                @finish="() => isLoading = false"
                :class="[
                    layout.currentRoute === dataNavigation[0].href.name ? `` : `bg-gray-100 hover:bg-gray-200 text-gray-600`
                ]"
                :style="{
                    backgroundColor: layout.currentRoute === dataNavigation[0].href.name ? layout?.app?.theme[4] + '22' : '',
                    color: layout.currentRoute === dataNavigation[0].href.name ? `color-mix(in srgb, ${layout?.app?.theme[4]} 50%, black)` : ''
                }"
            >
                <div v-if="dataNavigation[0].leftIcon" class="pr-1">
                    <FontAwesomeIcon v-if="isLoading === 'home'" icon="fad fa-spinner-third" v-tooltip="capitalize(dataNavigation[0].leftIcon.tooltip)" fixed-width aria-hidden="true" class="animate-spin" />
                    <FontAwesomeIcon v-else :icon="dataNavigation[0].leftIcon.icon" v-tooltip="capitalize(dataNavigation[0].leftIcon.tooltip)" fixed-width aria-hidden="true" class="" />
                </div>

                <div class="xl:whitespace-nowrap">
                    <span class="leading-none">{{ useTruncate(dataNavigation[0].label, 16) }}</span>

                    <span v-if="dataNavigation[0].number">
                        <template v-if="typeof dataNavigation[0].number == 'number'">
                            <template v-if="dataNavigation[0].number > 0">
                                ({{ locale.number(dataNavigation[0].number) }})
                            </template>
                            <template v-else>
                                <FontAwesomeIcon icon='fal fa-empty-set' class='' fixed-width aria-hidden='true' />
                            </template>
                        </template>
                        <template v-else>
                            ({{ dataNavigation[0].number }})
                        </template>
                    </span>
                </div>
            </component>
        </div> -->

        <!-- Tabs -->
        <div class="w-full flex">
            <TransitionGroup>
                <template v-for="subNav, itemIdx in dataNavigation" :key="'subNav' + itemIdx">
                    <component
                        v-if="subNav.align !== 'right'"
                        :is="subNav.href?.name ? Link : 'div'"
                        :href="subNav.href?.name ? route(subNav.href.name, subNav.href.parameters) : '#'"
                        @start="() => isLoading = itemIdx"
                        @finish="() => isLoading = false"
                        class="pt-2 pb-1.5 px-3 flex w-fit items-center gap-x-2 transition-all"
                        :class="[
                            layout.currentRoute.includes(subNav.root || 'xxxxxxxxxxxxxxxxxxxxxxxxxxx') || layout.currentRoute === subNav.href?.name ? 'tabSubNavActive' : 'tabSubNav',
                        ]"
                    >
                        <div v-if="subNav.leftIcon" class="">
                            <FontAwesomeIcon v-if="isLoading === itemIdx" icon="fad fa-spinner-third" v-tooltip="capitalize(subNav.leftIcon.tooltip)" fixed-width aria-hidden="true" class="text-base animate-spin" />
                            <FontAwesomeIcon v-else :icon="subNav.leftIcon.icon" v-tooltip="capitalize(subNav.leftIcon.tooltip)" class="text-base opacity-50" fixed-width aria-hidden="true" />
                        </div>
                        <div class="xl:whitespace-nowrap flex items-center gap-x-1.5">
                            <span class="leading-none text-sm xl:text-base">{{ subNav.label }}</span>
                            <div v-if="typeof subNav.number == 'number'"
                                class="inline-flex items-center w-fit rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="layout.currentRoute.includes(subNav.root || 'xxxxxxxxxxxxxxxxxxxxxxxxxxx') || layout.currentRoute === subNav.href?.name ? 'bg-indigo-100 ' : 'bg-gray-200 '"
                            >
                                {{ locale.number(subNav.number || 0) }}
                            </div>
                        </div>
                    </component>
                </template>
            </TransitionGroup>
        </div>

        <div class="flex">
            <TransitionGroup>
                <template v-for="subNav, itemIdx in dataNavigation" :key="'subNav' + itemIdx">

                    <component
                        v-if="subNav.align === 'right'"
                        :is="subNav.href?.name ? Link : 'div'"
                        :href="subNav.href?.name ? route(subNav.href.name, subNav.href.parameters) : '#'"
                        @start="() => isLoading = itemIdx"
                        @finish="() => isLoading = false"
                        class="py-1.5 px-3 flex items-center gap-x-2 transition-all"
                        :class="[
                            layout.currentRoute.includes(subNav.root || 'xxxxxxxxxxxxxxxxxxxxxxxxxxx') || layout.currentRoute === subNav.href?.name ? 'tabSubNavActive' : 'tabSubNav',
                        ]"
                    >
                        <div v-if="subNav.leftIcon" class="">
                            <FontAwesomeIcon v-if="isLoading === itemIdx" icon="fad fa-spinner-third" v-tooltip="capitalize(subNav.leftIcon.tooltip)" fixed-width aria-hidden="true" class="text-sm animate-spin" />
                            <FontAwesomeIcon v-else :icon="subNav.leftIcon.icon" v-tooltip="capitalize(subNav.leftIcon.tooltip)" class="text-sm opacity-50" fixed-width aria-hidden="true" />
                        </div>
                        <div class="xl:whitespace-nowrap flex items-center gap-x-1.5">
                            <span class="leading-none font-medium text-base">{{ subNav.label }}</span>
                            <div v-if="typeof subNav.number == 'number'"
                                class="inline-flex items-center w-fit rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="layout.currentRoute.includes(subNav.root || 'xxxxxxxxxxxxxxxxxxxxxxxxxxx') || layout.currentRoute === subNav.href?.name ? 'bg-indigo-100 ' : 'bg-gray-200 '"
                            >
                                {{ locale.number(subNav.number || 0) }}
                            </div>
                        </div>
                    </component>

                </template>
            </TransitionGroup>
        </div>

        <!-- <div class="hidden border-b border-gray-300 px-1 sm:flex flex-auto">&nbsp</div> -->

    </div>
</template>

<style lang="scss" scoped>
// .tabSubNavActive {
//     @apply px-2 bg-white border sm:border-b-transparent rounded-md sm:rounded-b-none sm:rounded-t-md border-gray-300;

//     color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 40%, black)`') !important;
// }

// .tabSubNav {
//     @apply px-2 sm:border border-transparent border-b-gray-300;

//     color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 80%, black)`') !important;

//     &:hover {
//         color: v-bind('`color-mix(in srgb, ${layout?.app?.theme[4]} 60%, black)`') !important;
//     }
// }

.tabSubNavActive {
    border-bottom: v-bind('`1px solid ${layout.app.theme[0]}`');
    color: v-bind('`${layout?.app?.theme[0]}`') !important;
}

.tabSubNav {
    @apply border-b border-transparent;

    color: #9ca3af !important;

    &:hover {
        color: #4b5563 !important;
    }
}


</style>