<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import NavigationSimple from '@/Layouts/NavigationSimple.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { Navigation } from '@/types/Navigation'
import { usePage } from '@inertiajs/vue3'
import { isRouteSameAsCurrentUrl } from '@/Composables/useUrl'


const props = defineProps<{
    orgNav: {
        [key: string]: Navigation[]
    }
    itemKey: string  // shop | warehouse
    icon: string

}>()

const layout = useLayoutStore()

// Generate string 'shop' to 'currentShop'
const generateCurrentString = (str: string) => {
    return 'current' + str.charAt(0).toUpperCase() + str.slice(1)
}

</script>

<template>
    <div class="relative isolate ring-1 ring-white/20 rounded transition-all duration-200 ease-in-out"
        :class="layout.leftSidebar.show ? 'px-1' : 'px-0'"
    >
        <!-- Label: Icon shops/warehouses and slug -->
        <div class="flex items-end gap-x-1.5 pt-2 px-2.5 mb-2 text-indigo-100/70">
            <FontAwesomeIcon v-if="icon" :icon='icon' class='text-xxs' fixed-width aria-hidden='true' />
            <span v-if="layout.leftSidebar.show" class="text-[9px] leading-none uppercase">
                {{ layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)] || Object.keys(orgNav)[0] }}
            </span>
        </div>

        <!-- {{ Object.keys(orgNav[layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)]]) }} -->
        <!-- If Shops/Warehouses length is 1 (Show the subnav straighly) -->
        <div v-if="Object.keys(orgNav || []).length === 1" class="flex flex-col gap-y-1 mb-1">
            group only 1
            <template v-for="nav, navIndex in orgNav[Object.keys(orgNav)[0]]">
                <NavigationSimple
                    :nav="nav"
                    :navKey="navIndex"
                />
                
                <div v-if="(nav.route?.name ? isRouteSameAsCurrentUrl(route(nav.route.name, nav.route.parameters)) : false)"
                    class="absolute inset-0 bg-black/20 rounded -z-10"
                />
            </template>
        </div>
        
        <!-- If Shops/Warehouses length is more than 1 and current warehouse is exist -->
        <div v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)]"
            class="flex flex-col gap-y-1 mb-1">
            <!-- Looping: SubNav -->
            <template v-for="nav, navIndex in orgNav[layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)]]">
                <NavigationSimple
                    :nav="nav"
                    :navKey="navIndex"
                />
                
                <div v-if="(nav.route?.name ? isRouteSameAsCurrentUrl(route(nav.route.name, nav.route.parameters)) : false)"
                    class="absolute inset-0 bg-black/20 rounded -z-10"
                />
            </template>
        </div>
    </div>
</template>