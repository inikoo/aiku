<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import { Navigation } from '@/types/Navigation'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {  } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link } from '@inertiajs/vue3'
// import SubNavigation from '@/Layouts/SubNavigation.vue'
import { capitalize } from "@/Composables/capitalize"

library.add()

const props = defineProps<{
    navKey: string | number  // shops_navigation | warehouses_navigation
    nav: Navigation
}>()

// From 'shops_index' to 'shops'
const separateUnderscore = (str: string | number) => {
    const realString = str.toString()

    return realString.split('_')
}

const layout = useLayoutStore()

</script>

<template>
    <Link :href="nav.route?.name ? route(nav.route.name, nav.route.parameters) : '#'"
        class="group flex items-center px-2 text-sm gap-x-2" :class="[
            separateUnderscore(navKey)[0] === layout.currentModule || layout.currentModule.includes(navKey.toString())  // 'shops' == 'shops' || 'shop' include in 'shops'
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
</template>