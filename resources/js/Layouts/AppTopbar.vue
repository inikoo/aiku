<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { useLayoutStore } from "@/Stores/layout"
import { ref, reactive } from 'vue'
import { get } from 'lodash'
import { useLiveUsers } from '@/Stores/active-users'
import {capitalize} from "@/Composables/capitalize"
import MenuPopoverList from "@/Layouts/MenuPopoverList.vue"
import TopbarSelectButton from "@/Layouts/TopbarSelectButton.vue"
import { useValueInDeepObject } from "@/Composables/useFindKeyObject"

import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue"
import { Disclosure } from "@headlessui/vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SearchBar from "@/Components/SearchBar.vue"
import { trans } from "laravel-vue-i18n"
import Image from "@/Components/Image.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronDown } from '@far'
import { faTerminal, faUserAlien, faCog, faCity, faBuilding, faNetworkWired, faUserHardHat, faCalendar, faStopwatch, faStoreAlt, faWarehouseAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useTruncate } from '../Composables/useTruncate'
library.add(faChevronDown, faTerminal, faUserAlien, faCog, faCity, faBuilding, faNetworkWired, faUserHardHat, faCalendar, faStopwatch, faStoreAlt, faWarehouseAlt)

const props = defineProps<{
    sidebarOpen: boolean
    logoRoute: string
    urlPrefix: string
}>()

defineEmits<{
    (e: 'sidebarOpen', value: boolean): void
}>()

// To handle skeleton image in dropdown
const imageSkeleton: {[key:string]: boolean} = reactive({})

const layout = useLayoutStore()
const showSearchDialog = ref(false)

const logoutAuth = () => {
    router.post(route(props.urlPrefix + 'logout'))

    const dataActiveUser = {
        ...layout.user,
        name: null,
        last_active: new Date(),
        action: 'logout',
        current_page: {
            label: trans('Logout'),
            url: null,
            icon_left: null,
            icon_right: null,
        },
    }
    window.Echo.join(`grp.live.users`).whisper('otherIsNavigating', dataActiveUser)
    useLiveUsers().unsubscribe()  // Unsubscribe from Laravel Echo
}

// For label
const label = {
    organisationSelect: trans('Select organisation'),
    shopSelect: trans('Go to shop'),
    warehouseSelect: trans('Select warehouses'),
    fulfilmentSelect: trans('Select fulfilments'),
}

</script>

<template>
    <Disclosure as="nav" class=" fixed top-0 z-[21] w-full bg-gray-50 text-gray-700" v-slot="{ open }">
        <div class="px-0">
            <div class="flex h-11 lg:h-10 flex-shrink-0">
                <div class="flex">
                    <!-- Mobile: Hamburger -->
                    <button class="block md:hidden w-10 h-10 relative focus:outline-none" @click="$emit('sidebarOpen', !sidebarOpen)">
                        <span class="sr-only">Open sidebar</span>
                        <div class="block w-5 absolute left-1/2 top-1/2   transform  -translate-x-1/2 -translate-y-1/2">
                            <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-200 ease-in-out"
                                :class="{'rotate-45': sidebarOpen,' -translate-y-1.5': !sidebarOpen }"></span>
                            <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-100 ease-in-out" :class="{'opacity-0': sidebarOpen } "></span>
                            <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-200 ease-in-out"
                                :class="{'-rotate-45': sidebarOpen, ' translate-y-1.5': !sidebarOpen}"></span>
                        </div>
                    </button>

                    <!-- App Title: Image and Title -->
                    <div class="bg-indigo-700 flex flex-1 items-center justify-center md:justify-start transition-all duration-300 ease-in-out"
                        :class="[layout.leftSidebar.show ? 'md:w-48 md:pr-4' : 'md:w-12']"
                    >
                        <Link :href="layout.app?.url ?? '#'"
                            class="hidden md:flex flex-nowrap items-center h-full overflow-hidden gap-x-1.5 transition-all duration-200 ease-in-out"
                            :class="[layout.leftSidebar.show ? 'py-1 pl-4' : 'pl-2.5 w-full']"
                        >
                            <Image :src="layout.organisations.data.find((item) => item.slug == layout.currentParams.organisation)?.logo || layout.group?.logo" class="aspect-square h-5"/>
                            <Transition name="slide-to-left">
                                <p v-if="layout.leftSidebar.show" class="bg-gradient-to-r from-white to-indigo-100 text-transparent text-lg bg-clip-text font-bold whitespace-nowrap leading-none lg:truncate">
                                    Aiku
                                </p>
                            </Transition>
                        </Link>
                    </div>
                </div>

                <div class="flex items-center w-full justify-between pr-6 space-x-3 border-b border-gray-200">

                    <!-- Section: Dropdown + subsections -->
                    <div class="flex items-center gap-x-2 pl-2">
                        <!-- Section: Dropdown -->
                        <div v-if="
                            layout.group
                            || (layout.organisations.data?.length > 1 ? true : false)
                            || useLayoutStore().organisations.data?.find(organisation => organisation.slug == layout.currentParams.organisation) && (route(useLayoutStore().currentRoute, useLayoutStore().currentParams)).includes('shops')
                            || useLayoutStore().navigation.org?.[layout.currentParams.organisation]?.warehouses_navigation && (route(useLayoutStore().currentRoute, useLayoutStore().currentParams)).includes('warehouse')
                        "
                        class="flex border border-gray-300 rounded-md">
                            <!-- Dropdown: Organisations -->
                            <Menu v-if="layout.group || (layout.organisations.data.length > 1 ? true : false)" as="div" class="relative inline-block text-left">
                                <TopbarSelectButton
                                    :icon="layout.currentParams.organisation ? 'fal fa-building' : 'fal fa-city'"
                                    :activeButton="!!(layout.organisations.data.find((item) => item.slug == layout.currentParams.organisation))"
                                    :label="layout.organisations.data.find((item) => item.slug == layout.currentParams.organisation)?.label ?? label.organisationSelect"
                                />
                                <transition>
                                    <MenuItems
                                        class="min-w-24 w-fit max-w-96 absolute left-0 mt-2 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <div class="px-1 py-1 space-y-2.5">
                                            <!-- Dropdown: Group -->
                                            <div v-if="layout.group" class="">
                                                <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                    <FontAwesomeIcon icon='fal fa-city' class='text-gray-400 text-xxs' aria-hidden='true' />
                                                    <span class="text-[9px] leading-none text-gray-400">Groups</span>
                                                    <hr class="w-full rounded-full border-slate-300">
                                                </div>
                                                <MenuItem v-slot="{ active }">
                                                    <div @click="() => router.visit(route('grp.dashboard.show'))" :class="[
                                                        !layout.currentParams.organisation ? 'bg-slate-300 text-slate-600' : active ? 'bg-slate-200/75 text-indigo-600' : 'text-slate-600']"
                                                        class="group flex w-full gap-x-2 items-center rounded pl-3 pr-2 py-2 text-sm cursor-pointer"
                                                    >
                                                        <FontAwesomeIcon icon='fal fa-city' class='' ariaa-hidden='true' />
                                                        <div class="space-x-1">
                                                            <span class="font-semibold">{{ layout.group?.label }}</span>
                                                            <span class="text-[9px] leading-none text-gray-400">({{ trans('Group') }})</span>
                                                        </div>
                                                    </div>
                                                </MenuItem>
                                            </div>

                                            <div v-if="layout.organisations.data.length > 1">
                                                <!-- Dropdown: Organisation -->
                                                <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                    <FontAwesomeIcon icon='fal fa-building' class='text-gray-400 text-xxs' aria-hidden='true' />
                                                    <span class="text-[9px] leading-none text-gray-400">{{ trans('Organisations') }}</span>
                                                    <hr class="w-full rounded-full border-slate-300">
                                                </div>
                                                <div class="max-h-52 overflow-y-auto space-y-1.5">
                                                    <MenuItem v-for="(item) in layout.organisations.data" v-slot="{ active }">
                                                        <div @click="() => router.visit(route('grp.org.dashboard.show', { organisation: item.slug }))" :class="[
                                                            item.slug == layout.currentParams.organisation ? 'bg-slate-300 text-slate-600' : 'text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600',
                                                            'group flex gap-x-2 w-full justify-start items-center rounded pl-2 pr-4 py-2 text-sm cursor-pointer',
                                                        ]">
                                                            <div class="h-5 aspect-square rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                                                                <Image v-show="imageSkeleton[item.slug]" :src="item.logo" @onLoadImage="() => imageSkeleton[item.slug] = true"/>
                                                                <div v-show="!imageSkeleton[item.slug]" class="skeleton w-5 h-5"/>
                                                            </div>
                                                            <div class="font-semibold whitespace-nowrap">{{ useTruncate(item.label, 20) }}</div>
                                                        </div>
                                                    </MenuItem>
                                                </div>
                                            </div>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>

                            <!-- Dropdown: Shops -->
                            <Menu v-if="useLayoutStore().organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation) && (route(useLayoutStore().currentRoute, useLayoutStore().currentParams)).includes('shops')"
                                as="div" class="relative inline-block text-left"
                                v-slot="{ close: closeMenu }"
                            >
                                <TopbarSelectButton
                                    icon="fal fa-store-alt"
                                    :activeButton="!!(layout.currentParams.shop)"
                                    :label="layout.organisationsState?.[layout.currentParams.organisation]?.currentShop || label.shopSelect"
                                    :key="`shop` + layout.currentParams.shop"
                                />

                                <transition>
                                    <MenuItems class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <MenuPopoverList icon="fal fa-store-alt" :navKey="'shop'" :closeMenu="closeMenu" />
                                    </MenuItems>
                                </transition>
                            </Menu>

                            <!-- Dropdown: Warehouse -->
                            <Menu v-if="Object.keys(useLayoutStore().navigation.org[layout.currentParams.organisation]?.warehouses_navigation || []).length > 1 && (route(useLayoutStore().currentRoute, useLayoutStore().currentParams)).includes('warehouses')"
                                as="div" class="relative inline-block text-left"
                                v-slot="{ close: closeMenu }"
                            >
                                <TopbarSelectButton
                                    icon="fal fa-warehouse-alt"
                                    :activeButton="!!(layout.currentParams.warehouse)"
                                    :label="useLayoutStore().organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.find(warehouse => warehouse.slug == layout.currentParams.warehouse)?.label ?? label.warehouseSelect"
                                />
                                <transition>
                                    <MenuItems class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <MenuPopoverList icon="fal fa-warehouse-alt" :navKey="'warehouse'" :closeMenu="closeMenu" />
                                    </MenuItems>
                                </transition>
                            </Menu>

                            <!-- Dropdown: Warehouse -->
                            <Menu v-if="Object.keys(useLayoutStore().navigation.org[layout.currentParams.organisation]?.fulfilments_navigation || []).length > 1 && (route(useLayoutStore().currentRoute, useLayoutStore().currentParams)).includes('fulfilment')"
                                as="div" class="relative inline-block text-left"
                                v-slot="{ close: closeMenu }"
                            >
                                <TopbarSelectButton
                                    icon="fal fa-warehouse-alt"
                                    :activeButton="!!(layout.currentParams.fulfilment)"
                                    :label="useLayoutStore().organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.find(fulfilment => fulfilment.slug == layout.currentParams.fulfilment)?.label ?? label.fulfilmentSelect"
                                />
                                <transition>
                                    <MenuItems class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <MenuPopoverList icon="fal fa-warehouse-alt" :navKey="'fulfilment'" :closeMenu="closeMenu" />
                                    </MenuItems>
                                </transition>
                            </Menu>
                        </div>

                        <!-- Section: Subsections -->
                        <div class="flex h-full">
                            <!-- {{layout.navigation.org[layout.currentParams.organisation][layout.currentModule].topMenu.subSections.length}} -->
                            <!-- {{ get('layout', ['navigation', 'org', get('layout', ['currentParams', 'organisation']), get('layout', ['currentModule']), 'topMenu', 'subSections'], false) }} -->
                            <template v-if="useValueInDeepObject(layout.navigation.org?.[layout.currentParams.organisation]?.[layout.currentModule], 'topMenu')?.subSections?.length">
                                <Link v-for="menu in useValueInDeepObject(layout.navigation.org?.[layout.currentParams.organisation]?.[layout.currentModule], 'topMenu').subSections"
                                    :href="route(menu.route.name, menu.route.parameters)"
                                    :id="get(menu, 'label', menu?.route.name)"
                                    class="group relative text-gray-700 group text-sm flex justify-end items-center cursor-pointer py-3 gap-x-2 px-4 md:px-4 lg:px-4"
                                    :class="[]"
                                    :title="capitalize(menu.tooltip ?? menu.label ?? '')">
                                    <div :class="[
                                        route(layout.currentRoute, route().v().params).includes(route(menu.route.name, menu.route.parameters))
                                        ? 'bottomNavigationActive'
                                        : 'bottomNavigation'
                                    ]"/>
                                    <!-- {{ route(menu.route.name, menu.route.parameters) }} -->
                                    <FontAwesomeIcon :icon="menu.icon"
                                        class="h-5 lg:h-3.5 w-auto group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
                                        aria-hidden="true"/>
                                    <span v-if="menu.label" class="hidden lg:inline capitalize whitespace-nowrap">{{ menu.label }}</span>
                                </Link>
                            </template>
                        </div>
                    </div>

                    <!-- Avatar Group -->
                    <div class="flex justify-between">
                        <div class="flex">
                            <!-- Button: Search -->
                            <button @click="showSearchDialog = !showSearchDialog" id="search"
                                    class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <span class="sr-only">{{ trans("Search") }}</span>
                                <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-search" size="lg" />
                                <SearchBar v-if="showSearchDialog" v-on:close="showSearchDialog = false" />
                            </button>
                            <!-- Button: Notifications -->
                            <!-- <button type="button"
                                    class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <span class="sr-only">{{ trans("View notifications") }}</span>
                                <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-bell" size="lg" />
                            </button> -->
                        </div>

                        <!-- Avatar Button -->
                        <Menu as="div" class="relative">
                            <MenuButton id="avatar-thumbnail"
                                class="flex max-w-xs overflow-hidden items-center rounded-full bg-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <span class="sr-only">{{ trans("Open user menu") }}</span>
                                <Image  class="h-8 w-8 rounded-full"
                                    :src="layout.user.avatar_thumbnail"
                                    alt="" />
                            </MenuButton>
                            <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                                        leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                <MenuItems class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-200 focus:outline-none">
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <div type="button" @click="router.visit(route(urlPrefix+'profile.show'))"
                                                :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">
                                                {{ trans("View profile") }}
                                            </div>
                                        </MenuItem>
                                    </div>
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <div @click="logoutAuth()"
                                                :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']"
                                            >
                                                {{ trans('Logout') }}
                                            </div>
                                        </MenuItem>
                                    </div>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                </div>
            </div>
        </div>
    </Disclosure>
</template>

