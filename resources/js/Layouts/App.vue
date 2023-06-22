<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 11 Aug 2022 11:08:49 Malaysia Time, Kuala Lumpur, Malaysia
  -  Reformatted: Fri, 03 Mar 2023 12:40:58 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->


<script setup>
import { ref, watchEffect } from "vue";
import {
    Menu,
    MenuButton,
    MenuItem,
    MenuItems
} from "@headlessui/vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import Button from "@/Components/Elements/Buttons/Button.vue";
import SearchBar from "@/Components/SearchBar.vue";
import AppFooter from "@/Layouts/AppFooter.vue"
import { usePage } from "@inertiajs/vue3";
import { useLayoutStore } from "@/Stores/layout";
import { useLocaleStore } from "@/Stores/locale";

import Breadcrumbs from "@/Components/Navigation/Breadcrumbs.vue";
import { loadLanguageAsync, trans } from "laravel-vue-i18n";
import { Link } from "@inertiajs/vue3";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
    faHome,
    faConveyorBeltAlt,
    faUserHardHat,
    faBars,
    faUsersCog,
    faTachometerAltFast,
    faInventory,
    faStoreAlt,
    faUser,
    faIndustry,
    faParachuteBox,
    faDollyEmpty,
    faShoppingCart,
    faAbacus,
    faChevronDown,
    faGlobe,
    faLanguage
} from "@/../private/pro-light-svg-icons";

import AppLeftSideBar from "@/Layouts/AppLeftSideBar.vue";
import AppTopBar from "@/Layouts/AppTopBar.vue";

library.add(
    faHome,
    faConveyorBeltAlt,
    faUserHardHat,
    faBars,
    faUsersCog,
    faTachometerAltFast,
    faInventory,
    faStoreAlt,
    faUser,
    faIndustry,
    faParachuteBox,
    faDollyEmpty,
    faShoppingCart,
    faAbacus,
    faChevronDown,
    faGlobe,
    faLanguage
);
const initialiseApp = () => {
    const layout = useLayoutStore();
    const locale = useLocaleStore();

    if (usePage().props.localeData) {
        loadLanguageAsync(usePage().props.localeData.language.code);
    }
    watchEffect(() => {
        if (usePage().props.layout) {
            layout.navigation = usePage().props.layout.navigation ?? null;
            layout.secondaryNavigation = usePage().props.layout.secondaryNavigation ?? null;
            if (usePage().props.layout.shopsInDropDown) {
                layout.shopsInDropDown = usePage().props.layout.shopsInDropDown.data ??
                    {};
            }
        }

        if (usePage().props.layoutCurrentShopSlug) {
            layout.currentShopSlug = usePage().props.layoutCurrentShopSlug;
        } else {
            if (route().params.hasOwnProperty("shop")) {
                layout.currentShopSlug = route().params["shop"];
            }
        }

        if (usePage().props.layoutShopsList) {
            layout.shops = usePage().props.layoutShopsList;
        }

        if (usePage().props.localeData) {
            locale.language = usePage().props.localeData.language;
            locale.languageOptions = usePage().props.localeData.languageOptions;
        }

        layout.currentShopData = layout.shops[layout.currentShopSlug] ?? {
            slug: null,
            name: trans("All shops"),
            code: trans("All")
        };

        if (usePage().props.tenant) {
            layout.tenant = usePage().props.tenant ?? null;
        }
    });
    return layout;
};

const layout = initialiseApp();
console.log(layout)
const sidebarOpen = ref(false);

const showSearchDialog = ref(false);

const user = ref(usePage().props.auth.user);


</script>

<template>
    <div class="min-h-full ">
        <Disclosure as="nav" class="bg-gray-100 fixed top-0 z-20 w-full" v-slot="{ open }">
            <div class="px-0">
                <div class="flex h-11 lg:h-10 flex-shrink-0 border-b border-gray-200 bg-white ">
                    <div class="flex flex-1">
                        <div class="flex flex-1 lg:justify-between">
                            <!-- Hamburger -->
                            <button class="block md:hidden w-10 h-10 relative focus:outline-none bg-white" @click="sidebarOpen = !sidebarOpen">
                                <span class="sr-only">Open sidebar</span>
                                <div class="block w-5 absolute left-1/2 top-1/2   transform  -translate-x-1/2 -translate-y-1/2">
                                    <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-200 ease-in-out"
                                          :class="{'rotate-45': sidebarOpen,' -translate-y-1.5': !sidebarOpen }"></span>
                                    <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-100 ease-in-out" :class="{'opacity-0': sidebarOpen } "></span>
                                    <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-200 ease-in-out"
                                          :class="{'-rotate-45': sidebarOpen, ' translate-y-1.5': !sidebarOpen}"></span>
                                </div>
                            </button>
                            <!-- Menu -->
                            <AppTopBar :tenantName="layout.tenant.name"/>
                        </div>

                        <!-- Avatar Group -->
                        <div class="flex items-center mr-6 space-x-3">
                            <div class="flex">
                                <!-- Search Button -->
                                <button @click="showSearchDialog = !showSearchDialog"
                                        class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                                    <span class="sr-only">{{ trans("Search") }}</span>
                                    <font-awesome-icon aria-hidden="true" icon="fa-regular fa-search" size="lg" />
                                    <SearchBar v-if="showSearchDialog" v-on:close="showSearchDialog = false" />
                                </button>

                                <!-- Notifications -->
                                <button type="button"
                                        class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                                    <span class="sr-only">{{ trans("View notifications") }}</span>
                                    <font-awesome-icon aria-hidden="true" icon="fa-regular fa-bell" size="lg" />
                                </button>
                            </div>

                            <!-- Avatar Button -->
                            <Menu as="div" class="relative">

                                <MenuButton
                                    class="flex max-w-xs items-center rounded-full bg-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                                    <span class="sr-only">{{ trans("Open user menu") }}</span>
                                    <img v-if="user.avatar" class="h-8 w-8 rounded-full"
                                         :src="route('media.group.show',user.avatar)"
                                         alt="" />
                                </MenuButton>

                                <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                    <MenuItems class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-200 focus:outline-none">
                                        <div class="py-1">
                                            <MenuItem v-slot="{ active,close }">
                                                <Link as="ul" type="button" :href="route('profile.show')" @click="close"
                                                      :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">
                                                    {{ trans("View profile") }}
                                                </Link>
                                            </MenuItem>

                                        </div>

                                        <div class="py-1">
                                            <MenuItem v-slot="{ active }">
                                                <Link as="ul" type="button" :href="route('dashboard.tv')" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">
                                                    DashTV
                                                </Link>
                                            </MenuItem>
                                        </div>
                                        <div class="py-1">
                                            <MenuItem v-slot="{ active }">
                                                <Link as="ul" type="button" method="post" :href="route('logout')"
                                                      :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">Logout
                                                </Link>
                                            </MenuItem>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>
                        </div>
                    </div>


                </div>
            </div>

            <DisclosurePanel class="md:hidden">
                <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
                    <DisclosureButton v-for="item in navigation" :key="item.name" as="a" :href="item.href"
                                      :class="[item.current ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white', 'block rounded-md px-3 py-2 text-base font-medium']"
                                      :aria-current="item.current ? 'page' : undefined">{{ item.name }}
                    </DisclosureButton>
                </div>
                <div class="border-t border-gray-700 pt-4 pb-3">
                    <div class="flex items-center px-5">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" :src="user.imageUrl" alt="" />
                        </div>
                        <div class="ml-3">

                            <div class="text-base font-medium leading-none text-white">{{ user.name }}</div>
                            <div class="text-sm font-medium leading-none text-gray-400">{{ user.email }}</div>
                        </div>

                        <Button type="button"
                                class=" p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            <span class="sr-only">{{ trans("View notifications") }}</span>
                            <font-awesome-icon aria-hidden="true" icon="fa-regular fa-bell" size="lg" />
                        </Button>
                        <Button type="button" class=" p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            <span class="sr-only">{{ trans("View notifications") }}</span>
                            <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-bell" size="lg" />

                        </Button>

                    </div>
                    <div class="mt-3 space-y-1 px-2">
                        <DisclosureButton v-for="item in userNavigation" :key="item.name" as="a" :href="item.href"
                                          class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                            {{ item.name }}
                        </DisclosureButton>
                    </div>
                </div>
            </DisclosurePanel>
        </Disclosure>

        <div>
            <div class="bg-gray-100/80 fixed top-0 w-screen h-screen z-10" v-if="sidebarOpen" @click="sidebarOpen = !sidebarOpen" />
            <AppLeftSideBar :currentRoute="route().current()" v-if="!sidebarOpen" class="hidden md:block" />
            <AppLeftSideBar :currentRoute="route().current()" class="-left-2/3 transition-all duration-100 ease-in-out z-20 block md:hidden" :class="{'left-[0]': sidebarOpen }" @click="sidebarOpen = !sidebarOpen" />
        </div>


        <main class="relative flex flex-col pt-16 pb-5 ml-0 md:ml-10 xl:ml-56">
            <Breadcrumbs class="fixed top-11 lg:top-10 z-[19] w-full"
                         :breadcrumbs="usePage().props.breadcrumbs??[]"
                         :navigation="usePage().props.navigation??[]"
            />
            <slot />
        </main>

    </div>
        <AppFooter />

</template>


