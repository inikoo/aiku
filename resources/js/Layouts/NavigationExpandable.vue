<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import { Navigation } from '@/types/Navigation'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faStoreAlt, faWarehouseAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link } from '@inertiajs/vue3'
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
} from '@headlessui/vue'

library.add(faStoreAlt, faWarehouseAlt)

const props = defineProps<{
    navKey: string  // shops_navigation | warehouses_navigation
    subNav: {
        [shopKey: string]: { [navShopKey: string]: Navigation; }
    }
}>()

const layout = useLayoutStore()
const navigationName = props.navKey.split('_')[0].slice(0, -1)  // shops_navigation to shop
</script>

<template>
    <Disclosure v-slot="{ open }" as="div"
        class="pl-4 pr-2 pb-2"
        :class="[navKey === layout.currentModule ? 'px-0.5' : '', layout.leftSidebar.show ? '' : '',]">
        <!-- Label Navigation: Shops/Warehouses -->
        <DisclosureButton as="div" class="flex items-center justify-between mb-1 cursor-pointer">
            <div class="leading-none capitalize text-white font-bold pb-1 select-none flex items-center gap-x-1">
                <FontAwesomeIcon v-if="navKey == 'shops_navigation'" icon='fal fa-store-alt' class='text-sm opacity-75'
                    aria-hidden='true' />
                <FontAwesomeIcon v-if="navKey == 'warehouses_navigation'" icon='fal fa-warehouse-alt'
                    class='text-sm opacity-75' aria-hidden='true' />
                {{ navKey.split('_')[0] }}
            </div>
            <FontAwesomeIcon icon='fal fa-chevron-down' class='text-white text-xs transition-all duration-100 ease-in-out'
                :class="[open ? 'rotate-180' : '']" aria-hidden='true' />
        </DisclosureButton>

        <!-- Looping: Subnav -->
        <transition>
            <DisclosurePanel>

                <!-- If shop not selected, then show all shops -->
                <template v-if="!layout.currentParams[navigationName]">
                    <div v-for="(navigationShopWarehouse, indexShopWarehouse) in subNav" :key="indexShopWarehouse"
                        class="qwezxc group flex flex-col justify-center text-sm py-0.5" :class="[
                            indexShopWarehouse === layout.currentModule ? '' : '',
                            layout.leftSidebar.show ? 'pl-3' : ''
                        ]"
                        :aria-current="indexShopWarehouse === layout.currentModule ? 'page' : undefined">
                        <p v-if="Object.keys(subNav).length > 1" class="capitalize text-white">{{ indexShopWarehouse }}</p>
                        <!-- Looping: Navigation in Shop -->
                        <Link v-for="(shopNavigation, navigationIndex) in navigationShopWarehouse"
                            :href="shopNavigation.route?.name ? route(shopNavigation.route.name, shopNavigation.route.parameters) : '#'"
                            class="group flex items-center text-sm py-2 rounded-md pl-2" :class="[
                                navigationIndex === layout.currentModule
                                    ? 'navigationActive px-0.5'
                                    : 'navigation px-1',
                                layout.leftSidebar.show ? Object.keys(subNav).length > 1 ? 'px-3' : 'pr-3' : '',
                            ]"
                            :aria-current="navigationIndex === layout.currentModule ? 'page' : undefined">
                        <div class="flex items-center pr-2">
                            <FontAwesomeIcon aria-hidden="true" class="flex-shrink-0 h-4 w-4" :icon="shopNavigation.icon" />
                        </div>
                        <span class="capitalize leading-none whitespace-nowrap"
                            :class="[layout.leftSidebar.show ? 'block md:block' : 'block md:hidden']">
                            {{ shopNavigation.label }}
                        </span>
                        </Link>
                    </div>
                </template>

                <!-- If shop selected, show only selected shop -->
                <template v-else>
                    <div class="zxcqwe group flex flex-col justify-center text-sm py-0.5"
                        :class="[layout.currentParams[navigationName] === layout.currentModule ? '' : '', layout.leftSidebar.show ? 'pl-3' : '',]"
                        :aria-current="layout.currentParams[navigationName] === layout.currentModule ? 'page' : undefined">
                        <p class="capitalize text-white">{{ layout.currentParams[navigationName] }}</p>
                        <!-- Looping: Navigation in Shop -->
                        <Link v-for="(shopNavigation, navigationIndex) in subNav[layout.currentParams[navigationName]]"
                            :href="shopNavigation.route?.name ? route(shopNavigation.route.name, shopNavigation.route.parameters) : '#'"
                            class="group flex items-center text-sm py-2" :class="[
                                navigationIndex === layout.currentModule
                                    ? 'navigationActive px-0.5'
                                    : 'navigation px-1',
                                layout.leftSidebar.show ? 'px-3' : '',
                            ]"
                            :aria-current="navigationIndex === layout.currentModule ? 'page' : undefined">
                        <div class="flex items-center px-2">
                            <FontAwesomeIcon aria-hidden="true" class="flex-shrink-0 h-4 w-4" :icon="shopNavigation.icon" />
                        </div>
                        <span class="capitalize leading-none whitespace-nowrap"
                            :class="[layout.leftSidebar.show ? 'block md:block' : 'block md:hidden']">
                            {{ shopNavigation.label }}
                        </span>
                        </Link>
                    </div>
                </template>
            </DisclosurePanel>
        </transition>
    </Disclosure>
</template>