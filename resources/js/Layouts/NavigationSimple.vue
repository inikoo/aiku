<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import { Navigation } from '@/types/Navigation'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {  } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link, usePage } from '@inertiajs/vue3'
// import SubNavigation from '@/Layouts/SubNavigation.vue'
import { capitalize } from "@/Composables/capitalize"
import { isRouteSameAsCurrentUrl } from '@/Composables/useUrl'
import { onMounted, ref, onUnmounted, computed } from 'vue'
import TopbarSubsections from '@/Layouts/TopbarSubsections.vue'

library.add()

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
const  isTopMenuActive = ref(false)

onMounted(() => {
    isTopMenuActive.value = true
    // console.log('NavigationSimple.vue', props.navKey, props.nav)
})

onUnmounted(() => {
    isTopMenuActive.value = false
})

const compRouteSameAsCurrentUrl = computed(() => {
    return props.nav.route?.name ? isRouteSameAsCurrentUrl(route(props.nav.route.name, props.nav.route.parameters)) : false
})
// console.log(route().v().route.uri)
</script>

<template>
    <!-- {{ Object.keys(navKey) }} -->
    <Link :href="nav.route?.name ? route(nav.route.name, nav.route.parameters) : '#'"
        class="group flex items-center px-2 text-sm gap-x-2" :class="[
            compRouteSameAsCurrentUrl
                ? 'navigationActive'
                : 'navigation',
            layout.leftSidebar.show ? '' : '',
        ]" :aria-current="navKey === layout.currentModule ? 'page' : undefined"
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
    <template v-if="compRouteSameAsCurrentUrl">
        <Teleport to="#TopbarSubsections" :disabled="!compRouteSameAsCurrentUrl">
            <TopbarSubsections
                v-if="nav.topMenu?.subSections"
                :subSections="nav.topMenu.subSections"
            />
        </Teleport>
    </template>
</template>