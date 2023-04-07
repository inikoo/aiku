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
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

import Breadcrumbs from '@/Components/Navigation/Breadcrumbs.vue';
import {trans} from 'laravel-vue-i18n';
import {Link} from '@inertiajs/vue3';
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
    faAbacus, faChevronDown, faCube, faGlobe,
} from '@/../private/pro-light-svg-icons';

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
    faGlobe,
);

const initialiseApp = inject('initialiseApp');
const layout = initialiseApp();

const sidebarOpen = ref(false);

import {Disclosure, DisclosureButton, DisclosurePanel} from '@headlessui/vue';
import AppLeftSideBar from '@/Layouts/AppLeftSideBar.vue';
import AppShopNavigation from '@/Layouts/AppShopNavigation.vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import SearchBar from '@/Components/SearchBar.vue';
import {usePage} from '@inertiajs/vue3';

const showSearchDialog = ref(false);

const user = ref(usePage().props.auth.user);

</script>

<template>

    <div class="min-h-full">
        <Disclosure as="nav" class="bg-gray-100" v-slot="{ open }">
            <div class=" px-0">

                <div class="flex h-11 lg:h-10 flex-shrink-0 border-b border-gray-200 bg-white ">

                    <div class="flex flex-1 justify-between pl-3 md:pl-0">

                        <div class="flex items-center">

                            <div class="hidden md:block  mb-3  ml-3 ">
                                <img class="h-6  mt-2 mb-1" src="/art/logo-color-trimmed.png" alt="Aiku"/>
                            </div>

                            <div class="font-logo hidden md:block  ml-2 w-30 mr-2  xl:hidden   whitespace-nowrap	   text-sm

                             xl:40
                             2xl:w-56
                            "
                            >
                                {{ layout.tenant.name }}
                            </div>
                            <AppShopNavigation :shops="layout.shops"/>
                        </div>

                        <div class="flex items-center">

                            <button v-on:click="showSearchDialog = !showSearchDialog"
                                    class=" p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                                <span class="sr-only">{{ trans('Search') }}</span>
                                <font-awesome-icon aria-hidden="true" icon="fa-regular fa-search" size="lg">
                                </font-awesome-icon>
                                <SearchBar v-if="showSearchDialog" v-on:close="showSearchDialog = false">
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
                                        <span class="sr-only">{{ trans('Open user menu') }}</span>
                                        <img v-if="user.data.avatar" class="h-8 w-8 rounded-full"
                                             :src="route('media.central.show',user.data.avatar)"
                                             alt=""/>

                                    </MenuButton>
                                </div>
                                <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                    <MenuItems class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-200 focus:outline-none">
                                        <div class="py-1">
                                            <MenuItem v-slot="{ active }">
                                                <Link as="ul" type="button" :href="route('profile.show')" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">
                                                    {{ trans('View profile') }}
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


                    <button type="button"
                            class="border-l border-gray-400 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-cyan-500 lg:hidden"
                            @click="sidebarOpen = true">
                        <span class="sr-only">Open sidebar</span>
                        <font-awesome-icon aria-hidden="true" icon="fa-regular fa-bars  " size="lg"/>

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
                            <img class="h-10 w-10 rounded-full" :src="user.imageUrl" alt=""/>
                        </div>
                        <div class="ml-3">

                            <div class="text-base font-medium leading-none text-white">{{ user.name }}</div>
                            <div class="text-sm font-medium leading-none text-gray-400">{{ user.email }}</div>
                        </div>

                        <Button type="button"
                                class=" p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            <span class="sr-only">{{ trans('View notifications') }}</span>
                            <font-awesome-icon aria-hidden="true" icon="fa-regular fa-bell" size="lg"/>
                        </Button>
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

        <AppLeftSideBar/>


        <main class="flex flex-col pl-0
        md:pl-10
        xl:pl-40
        2xl:pl-56
">
            <Breadcrumbs :breadcrumbs="$page.props.breadcrumbs??[]"/>
            <slot/>
        </main>


    </div>
</template>


