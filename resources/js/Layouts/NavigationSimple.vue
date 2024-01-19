<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import { Navigation } from '@/types/Navigation'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {  } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link } from '@inertiajs/vue3'
import SubNavigation from '@/Layouts/SubNavigation.vue'

library.add()

const props = defineProps<{
    navKey: string | number  // shops_navigation | warehouses_navigation
    nav: Navigation
}>()

const layout = useLayoutStore()

</script>

<template>
    <Link :href="nav.route?.name ? route(nav.route.name, nav.route.parameters) : '#'"
        class="group flex items-center text-sm py-2 pl-4 gap-x-2" :class="[
            navKey === layout.currentModule
                ? 'navigationActive'
                : 'navigation',
            layout.leftSidebar.show ? 'px-3' : '',
        ]" :aria-current="navKey === layout.currentModule ? 'page' : undefined">
        <FontAwesomeIcon aria-hidden="true" class="flex-shrink-0 h-4 w-4" fixed-width :icon="nav.icon" />
        <Transition>
            <span class="capitalize leading-none whitespace-nowrap"
                :class="[layout.leftSidebar.show ? 'block md:block' : 'block md:hidden']">
                {{ nav.label }}
            </span>
        </Transition>
    </Link>
</template>