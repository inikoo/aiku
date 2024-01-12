<script setup lang="ts">
import {Link, router} from "@inertiajs/vue3"
import { useLayoutStore } from "@/Stores/layout"
import { ref, reactive } from 'vue'
import { get } from 'lodash'
import { useLiveUsers } from '@/Stores/active-users'
import {capitalize} from "@/Composables/capitalize"


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
    useLiveUsers().unsubscribe()  // Unsubscribe from Laravel Echo
}
</script>

<template>
    <Disclosure as="nav" class=" fixed top-0 z-[21] w-full bg-gray-50 text-gray-700" v-slot="{ open }">
        <div class="px-0">
            <div class="flex h-11 lg:h-10 flex-shrink-0">
                <div class="border-b border-org-500 flex">
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
                    <div class="bg-gradient-to-t from-gray-200 to-gray-50 flex flex-1 items-center justify-center md:justify-start transition-all duration-200 ease-in-out"
                        :class="[layout.leftSidebar.show ? 'md:w-48 md:pr-4' : 'md:w-10']"
                    >
                        <Link :href="layout.app?.url ?? '/'"
                            class="hidden md:flex flex-nowrap items-center h-full overflow-hidden gap-x-1.5 transition-all duration-200 ease-in-out"
                            :class="[layout.leftSidebar.show ? 'py-1 pl-4' : 'pl-2.5 w-full']"
                        >
                            <Image :src="layout.organisations.data.find((item) => item.slug == layout.currentParams.organisation)?.logo ?? layout.group.logo" class="aspect-square h-5"/>
                            <!-- <img v-else src="@/../art/logo/logo-white-square.png" class="aspect-square h-5 opacity-60" alt=""> -->

                            <Transition>
                                <p v-if="layout.leftSidebar.show" class="bg-gradient-to-r from-indigo-700 to-indigo-500 text-transparent text-lg bg-clip-text font-bold whitespace-nowrap leading-none lg:truncate">
                                    Aiku
                                </p>
                            </Transition>
                        </Link>
                    </div>
                </div>

                <div class="flex items-center w-full justify-between pr-6 space-x-3 border-b border-gray-200">
                    <!-- Section: Top menu -->
                    <!-- <OrgTopBarNavs /> -->

                    <!-- Section: Dropdown organisation -->
                    <div class="flex items-center gap-x-2">
                        <!-- Section: Dropdown -->
                        <div class="pl-2 py-1 space-x-2">
                            <!-- Dropdown: Organisations -->
                            <Menu as="div" class="relative inline-block text-left">
                                <MenuButton
                                    class="inline-flex min-w-fit w-32 max-w-full whitespace-nowrap justify-between items-center gap-x-2 rounded px-2.5 py-2 text-xs font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                                    :class="[ layout.currentParams.organisation ? 'bg-indigo-500 text-white hover:bg-indigo-600' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 ring-1 ring-slate-300']"
                                >
                                    <div class="flex items-center gap-x-1">
                                        <FontAwesomeIcon v-if="layout.currentParams.organisation" icon='fal fa-building' class='opacity-60 text-xs' fixed-width aria-hidden='true' />
                                        <FontAwesomeIcon v-else icon='fal fa-city' class='opacity-60 text-xs' fixed-width aria-hidden='true' />
                                        {{ layout.organisations.data.find((item) => item.slug == layout.currentParams.organisation)?.name ?? 'Select organisation' }}
                                    </div>
                                    <!-- {{ layout.organisations.currentOrganisations ? layout.organisations.currentOrganisations : 'Select group or organisations' }} -->
                                    <FontAwesomeIcon icon='far fa-chevron-down' class='text-xs' aria-hidden='true' />
                                </MenuButton>
                                <transition>
                                    <MenuItems
                                        class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <div class="px-1 py-1 space-y-2.5">
                                            <!-- Dropdown: Group -->
                                            <div class="">
                                                <!-- <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                    <span class="text-[9px] leading-none text-gray-400">Groups</span>
                                                    <hr class="w-full rounded-full border-slate-300">
                                                </div> -->
                                                <MenuItem v-slot="{ active }">
                                                    <div @click="() => router.visit(route('grp.dashboard.show'))" :class="[
                                                        !layout.currentParams.organisation ? 'bg-indigo-500 text-white' : active ? 'bg-slate-200/75 text-indigo-600' : 'text-slate-600']"
                                                        class="group flex w-full gap-x-2 items-center rounded pl-3 pr-2 py-2 text-sm cursor-pointer"
                                                    >
                                                        <FontAwesomeIcon icon='fal fa-city' class='' ariaa-hidden='true' />
                                                        <span class="font-semibold">{{ layout.group.name }}</span>
                                                    </div>
                                                </MenuItem>
                                            </div>

                                            <div>
                                                <!-- Dropdown: Organisation -->
                                                <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                    <FontAwesomeIcon icon='fal fa-building' class='text-gray-400 text-xxs' aria-hidden='true' />
                                                    <span class="text-[9px] leading-none text-gray-400">{{ trans('Organisations') }}</span>
                                                    <hr class="w-full rounded-full border-slate-300">
                                                </div>
                                                <div class="max-h-52 overflow-y-auto space-y-1.5">
                                                    <MenuItem v-for="(item) in layout.organisations.data" v-slot="{ active }">
                                                        <div @click="() => router.visit(route('grp.org.dashboard.show', { organisation: item.slug }))" :class="[
                                                            item.slug == layout.currentParams.organisation ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600',
                                                            'group flex gap-x-2 w-full justify-start items-center rounded px-2 py-2 text-sm cursor-pointer',
                                                        ]">
                                                            <div class="h-5 rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                                                                <Image v-show="imageSkeleton[item.slug]" :src="item.logo" @onLoadImage="() => imageSkeleton[item.slug] = true"/>
                                                                <div v-show="!imageSkeleton[item.slug]" class="skeleton w-5 h-5"/>
                                                            </div>
                                                            <div class="font-semibold">{{ item.name }}</div>
                                                        </div>
                                                    </MenuItem>
                                                </div>
                                            </div>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>

                            <!-- Dropdown: Shops -->
                            <Menu v-if="useLayoutStore().navigation.org[layout.currentParams.organisation]?.shops_navigation && (route(useLayoutStore().currentRoute, useLayoutStore().currentParams)).includes('shops')"
                                as="div" class="relative inline-block text-left">
                                <MenuButton
                                    class="inline-flex min-w-fit w-32 max-w-full whitespace-nowrap justify-between items-center gap-x-2 rounded px-2.5 py-2 text-xs font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                                    :class="[ layout.currentParams.shop ? 'bg-indigo-500 text-white hover:bg-indigo-600' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 ring-1 ring-slate-300']"
                                >
                                    <div class="flex items-center gap-x-1">
                                        <FontAwesomeIcon icon='fal fa-store-alt' class='opacity-60 text-xs' fixed-width aria-hidden='true' />
                                        <!-- <FontAwesomeIcon v-else icon='fal fa-city' class='opacity-60 text-xs' fixed-width aria-hidden='true' /> -->
                                        {{ useLayoutStore().currentParams.shop ?? 'Select shop' }}
                                    </div>
                                    <!-- {{ layout.organisations.currentOrganisations ? layout.organisations.currentOrganisations : 'Select group or organisations' }} -->
                                    <FontAwesomeIcon icon='far fa-chevron-down' class='text-xs' aria-hidden='true' />
                                </MenuButton>
                                <transition>
                                    <MenuItems
                                        class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <div class="px-1 py-1 ">
                                            <!-- Dropdown: Organisation -->
                                            <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                <FontAwesomeIcon icon='fal fa-store-alt' class='text-gray-400 text-xxs' aria-hidden='true' />
                                                <span class="text-[9px] leading-none text-gray-400">{{ trans('Shops') }}</span>
                                                <hr class="w-full rounded-full border-slate-300">
                                            </div>
                                            <div class="max-h-52 overflow-y-auto space-y-1.5">
                                                <MenuItem v-for="(shop, shopIdx) in useLayoutStore().navigation.org[layout.currentParams.organisation].shops_navigation" v-slot="{ active }">
                                                    <div @click="() => router.visit(route(shop.shop.route?.name, shop.shop.route?.parameters))" :class="[
                                                        shopIdx == layout.currentParams.shop ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600',
                                                        'group flex gap-x-2 w-full justify-start items-center rounded px-2 py-2 text-sm cursor-pointer',
                                                    ]">
                                                        <!-- <div class="h-5 rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                                                            <Image v-show="imageSkeleton[shopIdx]" :src="item.logo" @onLoadImage="() => imageSkeleton[shopIdx] = true"/>
                                                            <div v-show="!imageSkeleton[shopIdx]" class="skeleton w-5 h-5"/>
                                                        </div> -->
                                                        <div class="font-semibold">{{ shopIdx }}</div>
                                                    </div>
                                                </MenuItem>
                                            </div>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>

                            <!-- Dropdown: Warehouse -->
                            <Menu v-if="useLayoutStore().navigation.org[layout.currentParams.organisation]?.warehouses_navigation && (route(useLayoutStore().currentRoute, useLayoutStore().currentParams)).includes('warehouse')"
                                as="div" class="relative inline-block text-left">
                                <MenuButton
                                    class="inline-flex min-w-fit w-32 max-w-full whitespace-nowrap justify-between items-center gap-x-2 rounded px-2.5 py-2 text-xs font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                                    :class="[ layout.currentParams.warehouse ? 'bg-indigo-500 text-white hover:bg-indigo-600' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 ring-1 ring-slate-300']"
                                >
                                    <div class="flex items-center gap-x-1">
                                        <FontAwesomeIcon icon='fal fa-warehouse-alt' class='opacity-60 text-xs' fixed-width aria-hidden='true' />
                                        <!-- <FontAwesomeIcon v-else icon='fal fa-city' class='opacity-60 text-xs' fixed-width aria-hidden='true' /> -->
                                        {{ useLayoutStore().currentParams.warehouse ?? 'Select warehouse' }}
                                    </div>
                                    <!-- {{ layout.organisations.currentOrganisations ? layout.organisations.currentOrganisations : 'Select group or organisations' }} -->
                                    <FontAwesomeIcon icon='far fa-chevron-down' class='text-xs' aria-hidden='true' />
                                </MenuButton>
                                <transition>
                                    <MenuItems
                                        class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <div class="px-1 py-1 ">
                                            <!-- Dropdown: Organisation -->
                                            <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                <FontAwesomeIcon icon='fal fa-warehouse-alt' class='text-gray-400 text-xxs' aria-hidden='true' />
                                                <span class="text-[9px] leading-none text-gray-400">{{ trans('Warehouses') }}</span>
                                                <hr class="w-full rounded-full border-slate-300">
                                            </div>
                                            <div class="max-h-52 overflow-y-auto space-y-1.5">
                                                <MenuItem v-for="(warehouse, warehouseIdx) in useLayoutStore().navigation.org[layout.currentParams.organisation].warehouses_navigation" v-slot="{ active }">
                                                    <div @click="() => router.visit(route(warehouse.warehouse.route?.name, warehouse.warehouse.route?.parameters))" :class="[
                                                        warehouseIdx == layout.currentParams.warehouse ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600',
                                                        'group flex gap-x-2 w-full justify-start items-center rounded px-2 py-2 text-sm cursor-pointer',
                                                    ]">
                                                        <!-- <div class="h-5 rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                                                            <Image v-show="imageSkeleton[warehouseIdx]" :src="item.logo" @onLoadImage="() => imageSkeleton[warehouseIdx] = true"/>
                                                            <div v-show="!imageSkeleton[warehouseIdx]" class="skeleton w-5 h-5"/>
                                                        </div> -->
                                                        <div class="font-semibold">{{ warehouseIdx }}</div>
                                                    </div>
                                                </MenuItem>
                                            </div>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>
                        </div>

                        <!-- Section: Subsections -->
                        <div class="flex h-full">
                        <!-- {{ layout.currentParams.organisation }} -->
                            <template v-if="layout.currentParams.organisation">
                                <Link v-for="menu in layout.navigation.org[layout.currentParams.organisation][layout.currentModule]?.topMenu.subSections"
                                    :href="'#'"
                                    :id="get(menu, 'label', menu?.route.name)"
                                    class="group relative text-gray-700 group text-sm flex justify-end items-center cursor-pointer py-3 gap-x-2 px-4 md:px-4 lg:px-4"
                                    :title="capitalize(menu.tooltip ?? menu.label ?? '')">
                                    <!-- <div :class="[
                                        route(layout.currentRoute, route().v().params).includes(route(menu.route.name,menu.route.parameters))
                                        ? 'bottomNavigationActive'
                                        : 'bottomNavigation'
                                    ]"/> -->
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

