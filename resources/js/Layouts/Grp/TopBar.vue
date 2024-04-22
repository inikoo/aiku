<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Feb 2024 07:54:36 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3"
import { reactive, inject } from "vue"
import MenuPopoverList from "@/Layouts/Grp/MenuPopoverList.vue"
import TopBarSelectButton from "@/Layouts/Grp/TopBarSelectButton.vue"
import { Menu, MenuItem, MenuItems } from "@headlessui/vue"
import { Disclosure } from "@headlessui/vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { trans } from "laravel-vue-i18n"
import Image from "@/Components/Image.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronDown } from "@far"
import { faTerminal, faUserAlien, faCog, faCity, faBuilding, faNetworkWired, faUserHardHat, faCalendar, faStopwatch, faStoreAlt, faWarehouseAlt, faChartNetwork, faFolderTree, faFolder, faCube, faUserPlus, faBox, faBoxesAlt, faMoneyCheckAlt, faCashRegister, faCoins, faFileInvoiceDollar, faReceipt, faPersonDolly, faPeopleArrows,
  faConciergeBell,faGarage} from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { useTruncate } from "@/Composables/useTruncate"
import MenuTopRight from "@/Layouts/Grp/MenuTopRight.vue"
import { layoutStructure } from '@/Composables/useLayoutStructure'

library.add(faChevronDown, faTerminal, faUserAlien, faCog, faCity, faBuilding, faNetworkWired, faUserHardHat, faCalendar, faStopwatch, faStoreAlt, faWarehouseAlt, faChartNetwork, faFolderTree, faFolder, faCube, faUserPlus,
    faBox, faBoxesAlt, faMoneyCheckAlt, faCashRegister, faCoins, faFileInvoiceDollar, faReceipt, faPersonDolly, faPeopleArrows,
    faConciergeBell,faGarage
);

const props = defineProps<{
    sidebarOpen: boolean
    logoRoute: string
    urlPrefix: string
}>()

defineEmits<{
    (e: "sidebarOpen", value: boolean): void
}>()

// To handle skeleton image in dropdown
const imageSkeleton: { [key: string]: boolean } = reactive({})

const layoutStore = inject('layout', layoutStructure)

// For label
const label = {
    // organisationSelect: trans("Select organisation"),
    // agentSelect: trans("Select Agent"),
    shopSelect: trans("Go to shop"),
    warehouseSelect: trans("Select warehouses"),
    fulfilmentSelect: trans("Select fulfilments")
}

// console.log('agexxxnts', layoutStore.currentParams?.organisation)
// console.log('agents', layoutStore.agents.data)
// console.log('agents', layoutStore.agents.data.find((item) => item.slug == layoutStore.currentParams?.organisation))

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
                    <div class="flex flex-1 items-center justify-center md:justify-start transition-all duration-300 ease-in-out"
                        :class="[layoutStore.leftSidebar.show ? 'md:w-48 md:pr-4' : 'md:w-12']"
                        :style="{
                            'background-color': layoutStore.app.theme[0],
                            'color': layoutStore.app.theme[1],
                            'border-bottom': `1px solid ${layoutStore.app.theme[2]}3F`
                        }"
                    >
                        <Link :href="layoutStore.currentParams?.organisation ? route('grp.org.dashboard.show', layoutStore.currentParams?.organisation) : route('grp.dashboard.show')"
                            class="hidden md:flex flex-nowrap items-center h-full overflow-hidden gap-x-1.5 transition-all duration-200 ease-in-out"
                            :class="[layoutStore.leftSidebar.show ? 'py-1 pl-4' : 'pl-2.5 w-full']"
                        >
                            <Image :src="layoutStore.organisations.data.find((item) => item.slug == (layoutStore.currentParams?.organisation || false))?.logo || layoutStore.group?.logo" class="aspect-square h-5" />
                            <Transition name="slide-to-left">
                                <p v-if="layoutStore.leftSidebar.show" class="text-lg bg-clip-text font-bold whitespace-nowrap leading-none lg:truncate">
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
                            layoutStore.group
                            || (layoutStore.organisations.data?.length > 1)
                            || (layoutStore.organisations.data?.find(organisation => organisation.slug == layoutStore.currentParams.organisation) && (route(layoutStore.currentRoute, layoutStore.currentParams)).includes('shops'))
                            || (layoutStore.navigation.org?.[layoutStore.currentParams.organisation]?.warehouses_navigation && (route(layoutStore.currentRoute, layoutStore.currentParams)).includes('warehouse'))
                        "
                            class="flex border border-gray-300 rounded-md">
                            <!-- Dropdown: Topbar -->
                            <Menu v-if="layoutStore.group || (layoutStore.organisations.data.length > 1)" as="div" class="relative inline-block text-left">
                                <TopBarSelectButton
                                    :icon="
                                        layoutStore.currentParams?.organisation
                                            ? layoutStore.organisations.data.find((item) => item.slug == layoutStore.currentParams?.organisation)?.label
                                                ? 'fal fa-building'
                                                : layoutStore.agents.data.find((item) => item.slug == layoutStore.currentParams?.organisation)?.label
                                                    ? 'fal fa-people-arrows'
                                                    : 'fal fa-city'
                                            : 'fal fa-city'
                                    "
                                    :activeButton="!!(layoutStore.organisations.data.find((item) => item.slug == layoutStore.currentParams?.organisation)) || !!layoutStore.agents.data.find((item) => item.slug == layoutStore.currentParams?.organisation)"
                                    :label="
                                        layoutStore.currentParams?.organisation
                                            ? layoutStore.organisations.data.find((item) => item.slug == layoutStore.currentParams?.organisation)?.label
                                                ?? layoutStore.agents.data.find((item) => item.slug == layoutStore.currentParams?.organisation)?.label
                                                ?? 'Select organisation/agent'
                                            : 'Select organisation/agent'
                                    "
                                />
                                <transition>
                                    <MenuItems
                                        class="min-w-24 w-fit max-w-96 absolute left-0 mt-2 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <div class="px-1 py-1 space-y-2.5">
                                            <!-- Dropdown: Group -->
                                            <div v-if="layoutStore.group" class="">
                                                <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                    <FontAwesomeIcon icon="fal fa-city" class="text-gray-400 text-xxs" aria-hidden="true" />
                                                    <span class="text-[9px] leading-none text-gray-400">Groups</span>
                                                    <hr class="w-full rounded-full border-slate-300">
                                                </div>
                                                <MenuItem v-slot="{ active }">
                                                    <div @click="() => router.visit(route('grp.dashboard.show'))" :class="[
                                                        !layoutStore.currentParams?.organisation ? 'bg-slate-300 text-slate-600' : active ? 'bg-slate-200/75 text-indigo-600' : 'text-slate-600']"
                                                        class="group flex w-full gap-x-2 items-center rounded pl-3 pr-2 py-2 text-sm cursor-pointer"
                                                    >
                                                        <FontAwesomeIcon icon="fal fa-city" class="" ariaa-hidden="true" />
                                                        <div class="space-x-1">
                                                            <span class="font-semibold">{{ layoutStore.group?.label }}</span>
                                                            <span class="text-[9px] leading-none text-gray-400">({{ trans("Group") }})</span>
                                                        </div>
                                                    </div>
                                                </MenuItem>
                                            </div>

                                            <!-- Dropdown: Organisation -->
                                            <div v-if="layoutStore.organisations.data.length > 1">
                                                <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                    <FontAwesomeIcon icon="fal fa-building" class="text-gray-400 text-xxs" aria-hidden="true" />
                                                    <span class="text-[9px] leading-none text-gray-400">{{ trans("Organisations") }}</span>
                                                    <hr class="w-full rounded-full border-slate-300">
                                                </div>
                                                <div class="max-h-52 overflow-y-auto space-y-1.5">
                                                    <MenuItem v-for="(item) in layoutStore.organisations.data" v-slot="{ active }">
                                                        <div @click="() => router.visit(route('grp.org.dashboard.show', { organisation: item.slug }))" :class="[
                                                            item.slug == layoutStore.currentParams?.organisation ? 'bg-slate-300 text-slate-600' : 'text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600',
                                                            'group flex gap-x-2 w-full justify-start items-center rounded pl-2 pr-4 py-2 text-sm cursor-pointer',
                                                        ]">
                                                            <div class="h-5 aspect-square rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                                                                <Image v-show="imageSkeleton[item.slug]" :src="item.logo" @onLoadImage="() => imageSkeleton[item.slug] = true" />
                                                                <div v-show="!imageSkeleton[item.slug]" class="skeleton w-5 h-5" />
                                                            </div>
                                                            <div class="font-semibold whitespace-nowrap">{{ useTruncate(item.label, 20) }}</div>
                                                        </div>
                                                    </MenuItem>
                                                </div>
                                            </div>

                                            <!-- Dropdown: Agents -->
                                            <div v-if="layoutStore.agents?.data?.length > 1">
                                                <div class="flex items-center gap-x-1.5 px-1 mb-1">
                                                    <FontAwesomeIcon icon="fal fa-people-arrows" class="text-gray-400 text-xxs" aria-hidden="true" />
                                                    <span class="text-[9px] leading-none text-gray-400">{{ trans("Agents") }}</span>
                                                    <hr class="w-full rounded-full border-slate-300">
                                                </div>
                                                <div class="max-h-52 overflow-y-auto space-y-1.5">
                                                    <MenuItem v-for="(item) in layoutStore.agents?.data" v-slot="{ active }">
                                                        <div @click="() => router.visit(route('grp.org.dashboard.show', { organisation: item.slug }))" :class="[
                                                            item.slug == layoutStore.currentParams?.organisation ? 'bg-slate-300 text-slate-600' : 'text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600',
                                                            'group flex gap-x-2 w-full justify-start items-center rounded pl-2 pr-4 py-2 text-sm cursor-pointer',
                                                        ]">
                                                            <div class="h-5 aspect-square rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                                                                <Image v-show="imageSkeleton[item.slug]" :src="item.logo" @onLoadImage="() => imageSkeleton[item.slug] = true" />
                                                                <div v-show="!imageSkeleton[item.slug]" class="skeleton w-5 h-5" />
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

                            <!-- {{ layoutStore.isShopPage && layoutStore.organisationsState[layoutStore.currentParams.organisation].currentShop }} -->
                            <!-- Dropdown: Shops and Fulfilment-->
                            <Menu v-if="layoutStore.currentParams?.organisation && (layoutStore.isShopPage || layoutStore.isFulfilmentPage)"
                                  as="div" class="relative inline-block text-left"
                                  v-slot="{ close: closeMenu }"
                            >
                                <TopBarSelectButton
                                    :icon="layoutStore.isFulfilmentPage ? 'fal fa-hand-holding-box' : 'fal fa-store-alt'"
                                    :activeButton="
                                        !!((layoutStore.isFulfilmentPage && layoutStore.organisationsState[layoutStore.currentParams.organisation].currentFulfilment)
                                        || (layoutStore.isShopPage && layoutStore.organisationsState[layoutStore.currentParams.organisation].currentShop))
                                    "
                                    :label="
                                        layoutStore.isFulfilmentPage
                                            ? layoutStore.organisationsState?.[layoutStore.currentParams.organisation]?.currentFulfilment || label.fulfilmentSelect
                                            : layoutStore.isShopPage
                                                ? layoutStore.organisationsState?.[layoutStore.currentParams.organisation]?.currentShop || label.shopSelect
                                                : 'Select shops/fulfilments'
                                    "
                                    :key="`shop` + layoutStore.currentParams.shop + layoutStore.currentParams.fulfilment"
                                />

                                <transition>
                                    <MenuItems class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-400 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <MenuPopoverList v-if="layoutStore.organisations.data.find(organisation => organisation.slug == layoutStore.currentParams.organisation)?.authorised_shops.length"
                                                         icon="fal fa-store-alt" :navKey="'shop'" :closeMenu="closeMenu" />
                                        <MenuPopoverList v-if="layoutStore.organisations.data.find(organisation => organisation.slug == layoutStore.currentParams.organisation)?.authorised_fulfilments.length"
                                                         icon="fal fa-hand-holding-box" :navKey="'fulfilment'" :closeMenu="closeMenu" />
                                    </MenuItems>
                                </transition>
                            </Menu>

                            <!-- Dropdown: Warehouse -->
                            <Menu
                                v-if="layoutStore.currentParams?.organisation && Object.keys(layoutStore.navigation.org[layoutStore.currentParams?.organisation]?.warehouses_navigation || []).length > 1 && (route(layoutStore.currentRoute, layoutStore.currentParams)).includes('warehouses')"
                                as="div" class="relative inline-block text-left"
                                v-slot="{ close: closeMenu }"
                            >
                                <TopBarSelectButton
                                    icon="fal fa-warehouse-alt"
                                    :activeButton="!!(layoutStore.currentParams.warehouse)"
                                    :label="layoutStore.organisations.data.find(organisation => organisation.slug == layoutStore.currentParams.organisation)?.authorised_warehouses.find(warehouse => warehouse.slug == layoutStore.currentParams.warehouse)?.label ?? label.warehouseSelect"
                                />
                                <transition>
                                    <MenuItems class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <MenuPopoverList icon="fal fa-warehouse-alt" :navKey="'warehouse'" :closeMenu="closeMenu" />
                                    </MenuItems>
                                </transition>
                            </Menu>

                            <!-- Dropdown: Fulfilment -->
                            <!-- <Menu v-if="Object.keys(layoutStore.navigation.org[layoutStore.currentParams.organisation]?.fulfilments_navigation || []).length > 1 && (route(layoutStore.currentRoute, layoutStore.currentParams)).includes('fulfilment')"
                                as="div" class="relative inline-block text-left"
                                v-slot="{ close: closeMenu }"
                            >
                                <TopBarSelectButton
                                    icon="fal fa-warehouse-alt"
                                    :activeButton="!!(layoutStore.currentParams.fulfilment)"
                                    :label="layoutStore.organisations.data.find(organisation => organisation.slug == layoutStore.currentParams.organisation)?.authorised_fulfilments.find(fulfilment => fulfilment.slug == layoutStore.currentParams.fulfilment)?.label ?? label.fulfilmentSelect"
                                />
                                <transition>
                                    <MenuItems class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <MenuPopoverList icon="fal fa-warehouse-alt" :navKey="'fulfilment'" :closeMenu="closeMenu" />
                                    </MenuItems>
                                </transition>
                            </Menu> -->
                        </div>

                        <!-- Section: Subsections (Something will teleport to this section) -->
                        <div class="flex h-full" id="TopBarSubsections">
                        </div>

                    </div>

                    <!-- Section: Search, Notification, Profile -->
                    <MenuTopRight :urlPrefix="urlPrefix" />
                </div>
            </div>
        </div>
    </Disclosure>
</template>

