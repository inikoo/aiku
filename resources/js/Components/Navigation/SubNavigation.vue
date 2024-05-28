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
    <div class="-mt-2 flex flex-col sm:flex-row">
        <div class="flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:gap-x-3 gap-y-1 items-end text-gray-400 text-xs">
            <div v-for="subNav, itemIdx in dataNavigation" class="flex items-center -ml-1 first:text-indigo-500"
                :class="[
                    layout.currentRoute.includes(subNav.href?.name) ? `text-gray-500` : ``
                ]"
            >
                <Link v-if="subNav.href?.name"
                    :href="route(subNav.href.name, subNav.href.parameters)"
                    class="relative group py-1 px-1.5 flex items-center hover:text-gray-600 transition-all"
                >
                    <FontAwesomeIcon v-if="subNav.leftIcon" :icon="subNav.leftIcon.icon" v-tooltip="capitalize(subNav.leftIcon.tooltip)" aria-hidden="true" class="pr-1" />
                    <MetaLabel :item="subNav" />
                    <!-- <div
                        class=""
                        :class="[
                            $page.url.startsWith((route(subNav.href.name, subNav.href.parameters)).replace(new RegExp(originUrl, 'g'), '')) ? `bottomNavigationActive` : `bottomNavigation`
                        ]"
                    /> -->
                    <div v-if="itemIdx !== 0" :class="[
                            layout.currentRoute.includes(subNav.href.name) ? `bottomNavigationSecondaryActive` : `bottomNavigationSecondary`
                        ]"
                    />
                </Link>
                
                <span v-else>
                    <FontAwesomeIcon v-if="subNav.leftIcon" :icon="subNav.leftIcon.icon" :title="capitalize(subNav.leftIcon.tooltip)" aria-hidden="true" class="pr-2" />
                    <MetaLabel :item="subNav" />
                </span>
            </div>
        </div>
    </div>
</template>