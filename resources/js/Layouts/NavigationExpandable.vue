<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import { Navigation } from '@/types/Navigation'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faStoreAlt, faWarehouseAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link } from '@inertiajs/vue3'
import SubNavigation from '@/Layouts/SubNavigation.vue'
import {
    Disclosure, DisclosureButton, DisclosurePanel,
    Popover, PopoverButton, PopoverPanel
} from '@headlessui/vue'

library.add(faStoreAlt, faWarehouseAlt)

const props = defineProps<{
    navKey: string  // shops_navigation | warehouses_navigation
    subNav: {
        [shopKey: string]: { [navShopKey: string]: Navigation; }
    }
}>()

const layout = useLayoutStore()
const navigationName = props.navKey.split('_')[0].slice(0, -1)  // shops_navigation to shops | warehouses_navigation to warehouses
</script>

<template>
    <component :is="layout.leftSidebar.show ? Disclosure : Popover" v-slot="{ open }" as="div"
        class="relative hover:bg-indigo-300/30"
        :class="[navKey === layout.currentModule ? 'px-0.5' : '']">
        <!-- Label Navigation: Shops/Warehouses -->
        <component :is="layout.leftSidebar.show ? DisclosureButton : PopoverButton" as="div"
            class="flex items-center justify-between cursor-pointer"
            :class="layout.leftSidebar.show ? 'pt-2 py-1 pl-4 pr-2' : open ? 'py-2 bg-indigo-600' : 'py-2 hover:bg-indigo-500'"
        >
            <div class="leading-none capitalize text-white font-bold pb-1 select-none flex items-center gap-x-1" :class="layout.leftSidebar.show ? '' : 'mx-auto'">
                <FontAwesomeIcon v-if="navKey == 'shops_navigation'" icon='fal fa-store-alt' class='text-sm opacity-65' aria-hidden='true' />
                <FontAwesomeIcon v-if="navKey == 'warehouses_navigation'" icon='fal fa-warehouse-alt' class='text-sm opacity-65' aria-hidden='true' />
                
                <span v-if="layout.leftSidebar.show">
                    <template v-if="useLayoutStore().organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${navigationName}s`].length === 1">
                        {{ useLayoutStore().organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${navigationName}s`][0].name }}
                    </template>
                    <template v-else>{{ navKey.split('_')[0] }}</template>
                </span>
            </div>
            <FontAwesomeIcon v-if="layout.leftSidebar.show" icon='fal fa-chevron-down' class='text-white text-xs transition-all duration-200 ease-in-out'
                :class="[open ? 'rotate-180' : '']" aria-hidden='true' />
        </component>

        <!-- Looping: Subnav -->
        <transition>
            <teleport to="#leftSidebar" :disabled="layout.leftSidebar.show ? true : false">
                <component :is="layout.leftSidebar.show ? DisclosurePanel : PopoverPanel"
                    :class="layout.leftSidebar.show ? 'mt-1' : 'absolute top-0 left-12 max-h-96 min-h-fit overflow-y-auto bg-indigo-300 rounded-lg overflow-hidden z-10 mt-3 w-64 px-3 py-2'"
                >
                    <!-- If shop/warehouse not selected, then show all -->
                    <template v-if="!layout.currentParams[navigationName]">
                        <!-- LeftSidebar: Popover (if minimize)-->
                        <div v-if="!layout.leftSidebar.show" class="flex items-center gap-x-1 text-white font-bold mt-1">
                            <span class="capitalize leading-none text-sm">{{ navigationName }}s</span>
                            <hr class="w-full border border-white rounded-full mt-1">
                        </div>

                        <!-- If: Length available is only 1 -->
                        <template v-if="useLayoutStore().organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${navigationName}s`].length === 1">
                            <SubNavigation v-for="(shopNavigation, navigationIndex) in subNav[useLayoutStore().organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${navigationName}s`][0].slug]"
                                :navigation="shopNavigation" :indexNav="navigationIndex">
                            </SubNavigation>
                        </template>

                        <!-- Else: Lengt available is more than 1 -->
                        <template v-else>
                            <div v-for="(navigationShopWarehouse, indexShopWarehouse) in useLayoutStore().organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${navigationName}s`]" :key="indexShopWarehouse"
                                class="group flex flex-col justify-center text-sm py-0.5 gap-y-1" :class="[
                                    navigationShopWarehouse.slug === layout.currentModule ? '' : '',
                                    layout.leftSidebar.show ? '' : ''
                                ]"
                                :aria-current="navigationShopWarehouse.slug === layout.currentModule ? 'page' : undefined">
                                <SubNavigation :navigation="navigationShopWarehouse" :indexNav="navigationShopWarehouse.slug" />
                            </div>
                        </template>
                    </template>
                    
                    <!-- If shop selected, show only selected shop -->
                    <template v-else>
                        <div class="group flex flex-col justify-center text-sm py-0.5 gap-y-1"
                            :class="[
                                layout.currentParams[navigationName] === layout.currentModule ? '' : '',
                            ]"
                            :aria-current="layout.currentParams[navigationName] === layout.currentModule ? 'page' : undefined">
                            <!-- <p class="bg-indigo-300 py-0.5 pl-1 capitalize text-slate-700 font-bold">{{ layout.currentParams[navigationName] }}</p> -->
                            
                            <!-- Looping: Navigation in Shop -->
                            <SubNavigation v-for="(shopNavigation, navigationIndex) in subNav[layout.currentParams[navigationName]]"
                                :navigation="shopNavigation" :indexNav="navigationIndex">
                            </SubNavigation>
                        </div>
                    </template>
                </component>
            </teleport>
        </transition>
    </component>
</template>