<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 11 Aug 2022 11:08:49 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->


<script setup>
import {ref, inject} from 'vue';
import {
    Dialog,
    DialogPanel,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue'
import { Link } from '@inertiajs/inertia-vue3';
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
    faIndustry
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
            faIndustry
);


const initialiseApp = inject('initialiseApp')
const layout= initialiseApp();




const sidebarOpen = ref(false)
</script>

<template>
    <div class="min-h-full">
        <TransitionRoot as="template" :show="sidebarOpen">
            <Dialog as="div" class="relative z-40 lg:hidden" @close="sidebarOpen = false">
                <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-600 bg-opacity-75" />
                </TransitionChild>

                <div class="fixed inset-0 flex z-40">
                    <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="-translate-x-full">
                        <DialogPanel class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-white">
                            <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
                                <div class="absolute top-0 right-0 -mr-12 pt-2">
                                    <button type="button" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" @click="sidebarOpen = false">
                                        <span class="sr-only">Close sidebar</span>
                                        <font-awesome-icon aria-hidden="true"  class="h-6 w-6 text-white"  icon="fa-regular fa-times"/>

                                    </button>
                                </div>
                            </TransitionChild>

                            <div class="pt-0 text-center text-sm font-semibold">
                                {{layout.tenant.name}}
                            </div>
                            <div class="mt-5 flex-1 h-0 overflow-y-auto">

                                <nav class="px-2">
                                    <div class="space-y-1">
                                        <Link v-for="item in layout.navigation" :key="item.name" :href="route(item.route)" :class="[item.current ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50', 'group flex items-center px-2 py-2 text-base leading-5 font-medium rounded-md']" :aria-current="item.current ? 'page' : undefined">
                                            <font-awesome-icon aria-hidden="true"  :class="[item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-3 flex-shrink-0 h-6 w-6']" :icon="item.icon"  size="lg" />
                                            {{ item.name }}
                                        </Link>
                                    </div>
                                    <!-- Actions mobile -->

                                    <div class="mt-8">
                                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider" id="mobile-actions-headline">{{trans('Actions')}}</h3>
                                        <div class="mt-1 space-y-1" role="group" aria-labelledby="mobile-actions-headline">
                                            <a v-for="action in layout.actions" :key="action.name" :href="route(action.href)" class="group flex items-center px-3 py-2 text-base leading-5 font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span :class="[action.color, 'w-2.5 h-2.5 mr-4 rounded-full']" aria-hidden="true" />
                                                <span class="truncate">
                                                    {{ action.name }}
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </nav>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                    <div class="flex-shrink-0 w-14" aria-hidden="true">
                        <!-- Dummy element to force sidebar to shrink to fit close icon -->
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Static sidebar for desktop -->
        <div class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 lg:border-r lg:border-gray-200  lg:pb-4 lg:bg-gray-100">

            <div class="pt-5 text-center text-sm font-semibold">
            {{layout.tenant.name}}
            </div>

            <!-- Sidebar component, swap this element with another sidebar if you like -->
            <div class="pt-5 h-0 flex-1 flex flex-col overflow-y-auto">


                <!-- Navigation desktop -->

                <nav class="px-3 ">
                    <div class="space-y-1">
                        <Link v-for="item in layout.navigation" :href="route(item.route,item['routeParameters'])" :key="item.name" :class="[item.current ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50', 'capitalize group flex items-center px-2 py-2 text-sm font-medium rounded-md']" :aria-current="item.current ? 'page' : undefined">
                            <font-awesome-icon aria-hidden="true"  :class="[item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-3 flex-shrink-0 h-6 w-6']" :icon="item.icon"  size="lg" />
                            {{ item.name }}
                        </Link>
                    </div>
                    <div class="mt-8">
                        <!-- Actions desktop -->

                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider" id="desktop-actions-headline">{{trans('Actions')}}</h3>
                        <div class="mt-1 space-y-1" role="group" aria-labelledby="desktop-actions-headline">
                            <a v-for="action in layout.actions" :key="action.name" :href="route(action.route)" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                <span :class="[action.color, 'w-2.5 h-2.5 mr-4 rounded-full']" aria-hidden="true" />
                                <span class="truncate">
                                    {{ action.name }}
                                </span>
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!-- Main column -->
        <div class="lg:pl-64 flex flex-col">
            <!-- Search header -->
            <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white border-b border-gray-200 ">
                <button type="button" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-purple-500 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">{{trans('Open sidebar')}}</span>
                    <font-awesome-icon aria-hidden="true"  class="h-6 w-6" icon="fa-regular fa-bars"/>

                </button>
                <div class="flex-1 flex justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex-1 flex">
                        <div class="flex-shrink-0 flex items-center px-4">
                            <img class="h-8 w-auto" src="/art/logo-name.png" alt="Pika" />
                        </div>
                    </div>
                    <div class="flex items-center">

                        <button type="button" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            <span class="sr-only">{{trans('Search')}}</span>
                            <font-awesome-icon aria-hidden="true"  icon="fa-regular fa-search"  size="lg" />

                        </button>

                        <button type="button" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            <span class="sr-only">View notifications</span>
                            <font-awesome-icon aria-hidden="true"  icon="fa-regular fa-bell"  size="lg" />

                        </button>


                        <!-- Profile dropdown

                        todo https://github.com/tailwindlabs/headlessui/issues/1630
                        -->
                        <Menu as="div" class="ml-3 relative" >
                            <div>
                                <MenuButton class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <span class="sr-only">Open user menu</span>
                                    <img v-if="$page.props.auth.user['avatar']"  class="h-8 w-8 rounded-full"
                                         :src="'/'+$page.props.auth.user['avatar']??null"
                                         alt="" />

                                    <span class="flex-1 ml-3 mr-1  flex-col min-w-0 hidden lg:flex">
                                        <span class="text-gray-900 text-sm font-medium truncate">{{ $page.props.auth.user.username }}</span>
                                    </span>
                                    <font-awesome-icon aria-hidden="true" icon="fa-regular fa-chevron-down" class="hidden flex-shrink-0 ml-1 h-4 w-4 text-gray-400 lg:block"/>

                                </MenuButton>
                            </div>
                            <div>
                            <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                <MenuItems  class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-200 focus:outline-none">
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <Link as="ul" type="button" :href="route('profile.show')" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">{{ trans('View profile')}}  </Link>
                                        </MenuItem>

                                    </div>

                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <Link as="ul" type="button" method="post"  :href="route('logout')" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">Logout</Link>
                                        </MenuItem>
                                    </div>
                                </MenuItems>
                            </transition>
                            </div>
                        </Menu>
                    </div>
                </div>
            </div>
            <main class="flex-1">
                <Breadcrumbs :breadcrumbs="$page.props.breadcrumbs??[]"/>


                <slot/>
            </main>
        </div>
    </div>
</template>


