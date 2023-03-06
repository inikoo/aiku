<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 11 Aug 2022 11:08:49 Malaysia Time, Kuala Lumpur, Malaysia
  -  Reformatted: Fri, 03 Mar 2023 12:40:58 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->


<script setup>
import {ref, inject} from 'vue';
import {
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
} from '@headlessui/vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

import Breadcrumbs from '@/Components/Navigation/Breadcrumbs.vue';
import {trans} from 'laravel-vue-i18n';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faHome,
    faDollyFlatbedAlt,
    faConveyorBeltAlt,
    faUsers,
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
    faAbacus, faChevronDown, faCube, faGlobe
} from "@/../private/pro-light-svg-icons";

library.add(
    faHome,
    faDollyFlatbedAlt,
    faConveyorBeltAlt,
    faUsers,
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

    faCube,
    faGlobe
);

const initialiseApp = inject("initialiseApp");
const layout = initialiseApp();

const sidebarOpen = ref(false);

import {Disclosure, DisclosureButton, DisclosurePanel} from '@headlessui/vue';
import AppLeftSideBar from '@/Layouts/AppLeftSideBar.vue';
import AppShopNavigation from '@/Layouts/AppShopNavigation.vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import SearchBar from '@/Components/SearchBar.vue';

const showComponent = ref(false)

</script>

<template>

    <div class="min-h-full">
        <Disclosure as="nav" class="bg-gray-100" v-slot="{ open }">
            <div class=" px-0">

                <div class="flex h-12 lg:h-10 flex-shrink-0 border-b border-gray-200 bg-white ">

                    <div class="flex flex-1 justify-between pl-3 md:pl-0">

                        <div class="flex items-center">

                            <div class="hidden md:block pt-0 mr-6 text-center text-sm font-bold w-64">
                                {{ layout.tenant.name }}
                            </div>

                            <AppShopNavigation :shops="layout.shops"/>


                        </div>

                        <div class="flex items-center">

                            <button v-on:click="showComponent = !showComponent"
                                    class=" p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                                <span class="sr-only">{{ trans("Search") }}</span>
                                <font-awesome-icon aria-hidden="true" icon="fa-regular fa-search" size="lg">
                                </font-awesome-icon>
                                <SearchBar v-if="showComponent" v-on:close="showComponent = false">
                                </SearchBar>
                            </button>

                            <button type="button" class=" p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                                <span class="sr-only">{{ trans('View notifications') }}</span>
                                <font-awesome-icon aria-hidden="true" icon="fa-regular fa-bell" size="lg"/>

                            </button>

                            <Menu as="div" class="relative ml-3 mr-6 hidden lg:block ">
                                <div>
                                    <MenuButton
                                        class="flex max-w-xs items-center rounded-full bg-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                                        <span class="sr-only">{{ trans("Open user menu") }}</span>

                                        <img v-if="$page.props.auth.user['avatar']" class="h-8 w-8 rounded-full"
                                             :src="$page.props.auth.user['avatar']??null"
                                             alt="" />

                                    </MenuButton>
                                </div>
                                <transition enter-active-class="transition ease-out duration-100"
                                            enter-from-class="transform opacity-0 scale-95"
                                            enter-to-class="transform opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75"
                                            leave-from-class="transform opacity-100 scale-100"
                                            leave-to-class="transform opacity-0 scale-95">
                                    <MenuItems
                                        class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                        <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                                            <a :href="item.href"
                                               :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']">{{ item.name
                                                }}</a>
                                        </MenuItem>
                                    </MenuItems>
                                </transition>
                            </Menu>

                        </div>
                    </div>


                    <button type="button"
                            class="border-l border-gray-400 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-cyan-500 lg:hidden"
                            @click="sidebarOpen = true">
                        <span class="sr-only">Open sidebar</span>
                        <font-awesome-icon aria-hidden="true" icon="fa-regular fa-bars  " size="lg" />

                    </button>

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
                            <font-awesome-icon aria-hidden="true" icon="fa-regular fa-bell" size="lg" /></Button>
                        <Button type="button" class=" p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            <span class="sr-only">{{ trans('View notifications') }}</span>
                            <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-bell" size="lg"/>

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

        <div
            class="mt-10 hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:border-r lg:border-gray-200 lg:bg-gray-100 lg:pt-0 lg:pb-4">
            <AppLeftSideBar />
        </div>
        <div class="flex flex-col lg:pl-64">
            <main>
                <Breadcrumbs :breadcrumbs="$page.props.breadcrumbs??[]" />
                <slot />
            </main>
        </div>


    </div>
</template>


