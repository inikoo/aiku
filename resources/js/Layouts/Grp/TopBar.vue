<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Feb 2024 07:54:36 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { reactive, inject } from "vue"
import MenuPopoverList from "@/Layouts/Grp/MenuPopoverList.vue"
import TopBarSelectButton from "@/Layouts/Grp/TopBarSelectButton.vue"
import { Menu, MenuItems } from "@headlessui/vue"
import { Disclosure } from "@headlessui/vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { trans } from "laravel-vue-i18n"
import Image from "@/Components/Image.vue"
import { faChevronDown } from "@far"
import { faTerminal, faUserAlien, faCog, faCity, faBuilding, faNetworkWired, faUserHardHat, faCalendar, faStopwatch, faStoreAlt, faWarehouseAlt, faChartNetwork, faFolderTree, faFolder, faCube, faUserPlus, faBox, faBoxesAlt, faMoneyCheckAlt, faCashRegister, faCoins, faFileInvoiceDollar, faReceipt, faPersonDolly, faPeopleArrows,
  faConciergeBell,faGarage,faHamsa,faCodeMerge,faSortShapesDownAlt, faHatChef, faTags, faCommentDollar, faNewspaper, faMailBulk, faBell, faLaptopHouse,faHandHoldingBox,faStream,
  faShippingFast,faChessClock,faHouseDamage
} from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import MenuTopRight from "@/Layouts/Grp/MenuTopRight.vue"
import TopBarDropdownScope from "@/Layouts/Grp/TopBarDropdownScope.vue"
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { faBallot } from "@fas"

library.add(faChevronDown, faTerminal, faUserAlien, faCog, faCity, faBuilding, faNetworkWired, faUserHardHat, faCalendar, faStopwatch, faStoreAlt, faWarehouseAlt, faChartNetwork, faFolderTree, faFolder, faCube, faUserPlus,
    faBox, faBoxesAlt, faMoneyCheckAlt, faCashRegister, faCoins, faFileInvoiceDollar, faReceipt, faPersonDolly, faPeopleArrows,faStream,
    faConciergeBell,faGarage,faHamsa,faCodeMerge,faSortShapesDownAlt, faHatChef,faTags, faCommentDollar, faNewspaper, faMailBulk, faBell, faLaptopHouse,faHandHoldingBox,
  faShippingFast,faChessClock,faBallot,faHouseDamage
);

defineProps<{
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
                            <Image :src="layoutStore.organisations.data?.find((item) => item.slug == (layoutStore.currentParams?.organisation || false))?.logo || layoutStore.group?.logo" class="aspect-square h-5" />
                            <Transition name="slide-to-left">
                                <p v-if="layoutStore.leftSidebar.show" class="text-lg bg-clip-text font-bold whitespace-nowrap leading-none lg:truncate">
                                    {{ layoutStore.currentParams?.organisation
                                        ? layoutStore.organisations.data.find((item) => item.slug == layoutStore.currentParams?.organisation)?.label
                                            ?? layoutStore.agents.data.find((item) => item.slug == layoutStore.currentParams?.organisation)?.label
                                            ?? layoutStore.group?.label
                                        : layoutStore.group?.label }}
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
                                    :activeButton="!!(layoutStore.organisations.data?.find((item) => item.slug == layoutStore.currentParams?.organisation)) || !!layoutStore.agents.data?.find((item) => item.slug == layoutStore.currentParams?.organisation)"
                                    :label="
                                        layoutStore.currentParams?.organisation
                                            ? layoutStore.organisations.data.find((item) => item.slug == layoutStore.currentParams?.organisation)?.label
                                                ?? layoutStore.agents.data.find((item) => item.slug == layoutStore.currentParams?.organisation)?.label
                                                ?? layoutStore.group?.label
                                            : layoutStore.group?.label
                                    "
                                />
                                <transition>
                                    <MenuItems
                                        class="px-1 py-1 space-y-2.5 min-w-24 w-fit max-w-96 absolute left-0 mt-2 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <!-- Dropdown: Group -->
                                        <TopBarDropdownScope v-if="layoutStore.group" class=""
                                            :menuItems="[{
                                                label: layoutStore.group?.label,
                                            }]"
                                            menuKey="group"
                                            :imageSkeleton="imageSkeleton"
                                            :label="trans('groups')"
                                            icon="fal fa-city"
                                        />

                                        <!-- Dropdown: Organisation -->
                                        <TopBarDropdownScope v-if="layoutStore.organisations.data.length"
                                            :menuItems="layoutStore.organisations.data"
                                            :imageSkeleton="imageSkeleton"
                                            :label="trans('organisations')"
                                            icon="fal fa-building"
                                        />


                                        <!-- Dropdown: Agents -->
                                        <TopBarDropdownScope v-if="layoutStore.agents?.data?.length"
                                            :menuItems="layoutStore.agents?.data"
                                            :imageSkeleton="imageSkeleton"
                                            :label="trans('agents')"
                                            icon="fal fa-people-arrows"
                                        />

                                        <!-- Dropdown: Digital Agency -->
                                        <TopBarDropdownScope v-if="layoutStore.digital_agency?.data?.length"
                                            :menuItems="layoutStore.digital_agency?.data"
                                            :imageSkeleton="imageSkeleton"
                                            :label="trans('digital agency')"
                                            icon="fal fa-laptop-house"
                                        />
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

