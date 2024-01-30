<script setup lang='ts'>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { useLayoutStore } from '@/Stores/layout'
import NavigationSimple from '@/Layouts/NavigationSimple.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { Navigation } from '@/types/Navigation'
import { usePage } from '@inertiajs/vue3'
import { isRouteSameAsCurrentUrl } from '@/Composables/useUrl'
import { generateCurrentString } from '@/Composables/useConvertString'
import { ref } from 'vue'


const props = defineProps<{
    orgNav: {
        [key: string]: Navigation[]
    }
    itemKey: string  // shop | warehouse
    icon: string

}>()

const layout = useLayoutStore()
const isPanelOpen = ref(layout.organisationsState?.[layout.currentParams.organisation]?.currentType == props.itemKey)

</script>

<template>
    <Disclosure >
        <div class="relative isolate ring-1 ring-white/20 rounded transition-all duration-200 ease-in-out"
            :class="layout.leftSidebar.show ? 'px-1' : 'px-0'"
        >
            <!-- Label: Icon shops/warehouses and slug -->
            <DisclosureButton @click="isPanelOpen = !isPanelOpen" class="w-full flex justify-between items-end pt-2 px-2.5 pb-2 text-indigo-100/70">
                <div class="flex gap-x-1.5">
                    <FontAwesomeIcon v-if="icon" :icon='icon' class='text-xxs' fixed-width aria-hidden='true' />
                    <span v-if="layout.leftSidebar.show" class="text-[9px] leading-none uppercase">
                        {{ layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)] || Object.keys(orgNav)[0] }}
                    </span>
                </div>
                <FontAwesomeIcon icon='fal fa-chevron-down' class='justify-self-end text-xs transition-all duration-200 ease-in-out'
                    :class="[isPanelOpen ? 'rotate-180' : '']" aria-hidden='true' />
            </DisclosureButton>

            <!-- {{ Object.keys(orgNav[layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)]]) }} -->
            <!-- If Shops/Warehouses length is 1 (Show the subnav straighly) -->
            <div v-show="isPanelOpen">
                <DisclosurePanel static v-if="Object.keys(orgNav || []).length === 1" class="flex flex-col gap-y-1 mb-1">
                    <!-- group only 1 -->
                    <template v-for="nav, navIndex, index in orgNav[Object.keys(orgNav)[0]]" :key="navIndex + index">
                        <NavigationSimple
                            :nav="nav"
                            :navKey="navIndex"
                        />
                    </template>
                </DisclosurePanel>
                
                <!-- If Shops/Warehouses length is more than 1 and current warehouse is exist -->
                <DisclosurePanel static v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)]"
                    class="flex flex-col gap-y-1 mb-1">
                    <!-- Looping: SubNav -->
                    <template v-for="nav, navIndex in orgNav[layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(itemKey)]]" :key="navIndex">
                        <NavigationSimple
                            :nav="nav"
                            :navKey="navIndex"
                        />
                
                        <div v-if="(nav.route?.name ? isRouteSameAsCurrentUrl(route(nav.route.name, nav.route.parameters)) : false)"
                            class="absolute inset-0 bg-black/20 rounded -z-10"
                        />
                    </template>
                </DisclosurePanel>
            </div>
        
            <div v-if="Object.values(orgNav[Object.keys(orgNav)[0]]).some(nav => (nav.route?.name ? isRouteSameAsCurrentUrl(route(nav.route.name, nav.route.parameters)) : false))"
                class="absolute inset-0 bg-black/20 rounded -z-10"
            />
        </div>
    </Disclosure>
</template>