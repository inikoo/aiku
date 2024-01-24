<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import NavigationSimple from '@/Layouts/NavigationSimple.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { Navigation } from '@/types/Navigation'

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
    <div class="bg-black/15 rounded pt-1 space-y-1 transition-all duration-200 ease-in-out"
        :class="layout.leftSidebar.show ? 'px-1' : 'px-0'"
    >
        <!-- Label: Icon shops/warehouses and slug -->
        <div class="flex items-end gap-x-1.5 mt-1 px-2.5 mb-2 text-indigo-100/70">
            <FontAwesomeIcon v-if="icon" :icon='icon' class='text-xxs' fixed-width aria-hidden='true' />
            <span v-if="layout.leftSidebar.show" class="text-[9px] leading-none uppercase">
                {{ layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)] || Object.keys(orgNav)[0] }}
            </span>
        </div>

        <!-- If Shops/Warehouses length is 1 (Show the subnav straighly) -->
        <template v-if="Object.keys(orgNav || []).length === 1">
            <NavigationSimple v-for="nav, navIndex in orgNav[Object.keys(orgNav)[0]]"
                :nav="nav"
                :navKey="navIndex"
            />
        </template>
        
        <!-- If Shops/Warehouses length is more than 1 and current warehouse is exist -->
        <template v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)]">
            <NavigationSimple v-for="nav, navIndex in orgNav[layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)]]"
                :nav="nav"
                :navKey="navIndex"
            />
        </template>
    </div>
</template>