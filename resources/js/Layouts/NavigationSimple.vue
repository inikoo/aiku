<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import { Navigation } from '@/types/Navigation'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faRoute } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link, usePage, router } from '@inertiajs/vue3'
// import SubNavigation from '@/Layouts/SubNavigation.vue'
import { capitalize } from "@/Composables/capitalize"
import { isNavigationActive } from '@/Composables/useUrl'
import { onMounted, ref, onUnmounted, computed } from 'vue'
import TopbarSubsections from '@/Layouts/TopbarSubsections.vue'
import {faHandHoldingBox} from '@fal';
library.add(faRoute, faHandHoldingBox)

const props = defineProps<{
    navKey: string | number  // shops_navigation | warehouses_navigation
    nav: Navigation
}>()

// From 'shops_index' to 'shops'
// const separateUnderscore = (str: string | number) => {
//     const realString = str.toString()

//     return realString.split('_')
// }

const layout = useLayoutStore()
const isTopMenuActive = ref(false)

onMounted(() => {
    isTopMenuActive.value = true
    // console.log('NavigationSimple.vue', props.navKey, props.nav)
})

onUnmounted(() => {
    isTopMenuActive.value = false
})

// Check if this route has nav.root
// const isRouteActive = () => {
//     return (layout.currentRoute).includes(props.nav.root || 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')  // 'aaaa' so it will return false
// }


</script>

<template>
    <!-- {{ layout.currentRoute }} -->
    <!-- <div class="text-xxs">{{ layout.currentRoute }} <br> {{ nav.route.name }}</div> -->
    <Link :href="nav.route?.name ? route(nav.route.name, nav.route.parameters) : '#'"
        class="group flex items-center px-2 text-sm gap-x-2" :class="[
            isNavigationActive(props.nav.root)
                ? 'navigationActive'
                : 'navigation',
            layout.leftSidebar.show ? '' : '',
        ]"
        :style="[isNavigationActive(props.nav.root) ? {
            'background-color': layout.app?.theme[1],
            'color': layout.app?.theme[2]
        } : {} ]"
        
        :aria-current="navKey === layout.currentModule ? 'page' : undefined"
        v-tooltip="layout.leftSidebar.show ? false : capitalize(nav.label)"
    >
        <FontAwesomeIcon v-if="nav.icon" aria-hidden="true" class="flex-shrink-0 h-4 w-4" fixed-width :icon="nav.icon" />
        <Transition name="slide-to-left">
            <span v-if="layout.leftSidebar.show" class="capitalize leading-none whitespace-nowrap block md:block"
                :class="[layout.leftSidebar.show ? '' : 'block md:hidden']">
                {{ nav.label }}
            </span>
            <span v-else class="capitalize leading-none whitespace-nowrap block md:hidden">
                {{ nav.label }}
            </span>
        </Transition>
    </Link>

    <!-- If this Navigation is active, then teleport the SubSections to #TopbarSubsections in <AppTopBar> -->
    <template v-if="isNavigationActive(props.nav.root || 'xx.xx.xx.xx')">
        <Teleport to="#TopbarSubsections" :disabled="!isNavigationActive(props.nav.root || 'xx.xx.xx.xx')">
            <TopbarSubsections
                v-if="nav.topMenu?.subSections"
                :subSections="nav.topMenu.subSections"
            />
        </Teleport>
    </template>
</template>
