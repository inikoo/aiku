
<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 11 Aug 2022 11:08:49 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->


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
                                        <XIcon class="h-6 w-6 text-white" aria-hidden="true" />
                                    </button>
                                </div>
                            </TransitionChild>

                            <div class="pt-0 text-center text-sm font-semibold">
                                {{layout.organisation.name}}
                            </div>
                            <div class="mt-5 flex-1 h-0 overflow-y-auto">

                                <nav class="px-2">
                                    <div class="space-y-1">
                                        <Link v-for="item in layout.navigation" :key="item.name" :href="route(item.route)" :class="[item.current ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50', 'group flex items-center px-2 py-2 text-base leading-5 font-medium rounded-md']" :aria-current="item.current ? 'page' : undefined">
                                            <font-awesome-icon aria-hidden="true"  :class="[item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-3 flex-shrink-0 h-6 w-6']" :icon="item.icon"  size="lg" />
                                            {{ item.name }}
                                        </Link>
                                    </div>
                                    <div class="mt-8">
                                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider" id="mobile-teams-headline">Teams</h3>
                                        <div class="mt-1 space-y-1" role="group" aria-labelledby="mobile-teams-headline">
                                            <a v-for="team in teams" :key="team.name" :href="team.href" class="group flex items-center px-3 py-2 text-base leading-5 font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span :class="[team.bgColorClass, 'w-2.5 h-2.5 mr-4 rounded-full']" aria-hidden="true" />
                                                <span class="truncate">
                                                    {{ team.name }}
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
            {{layout.organisation.name}}
            </div>

            <!-- Sidebar component, swap this element with another sidebar if you like -->
            <div class="pt-5 h-0 flex-1 flex flex-col overflow-y-auto">


                <!-- Navigation desktop -->

                <nav class="px-3 ">
                    <div class="space-y-1">
                        <Link v-for="item in layout.navigation" :href="route(item.route)" :key="item.name" :class="[item.current ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50', 'group flex items-center px-2 py-2 text-sm font-medium rounded-md']" :aria-current="item.current ? 'page' : undefined">
                            <font-awesome-icon aria-hidden="true"  :class="[item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-3 flex-shrink-0 h-6 w-6']" :icon="item.icon"  size="lg" />
                            {{ item.name }}
                        </Link>
                    </div>
                    <div class="mt-8">
                        <!-- Secondary navigation -->
                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider" id="desktop-teams-headline">Teams</h3>
                        <div class="mt-1 space-y-1" role="group" aria-labelledby="desktop-teams-headline">
                            <a v-for="team in teams" :key="team.name" :href="team.href" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                <span :class="[team.bgColorClass, 'w-2.5 h-2.5 mr-4 rounded-full']" aria-hidden="true" />
                                <span class="truncate">
                                    {{ team.name }}
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
                    <span class="sr-only">Open sidebar</span>
                    <MenuAlt1Icon class="h-6 w-6" aria-hidden="true" />
                </button>
                <div class="flex-1 flex justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex-1 flex">
                        <div class="flex-shrink-0 flex items-center px-4">
                            <img class="h-8 w-auto" src="/art/logo-name.png" alt="Pika" />
                        </div>
                    </div>
                    <div class="flex items-center">

                        <button type="button" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            <span class="sr-only">Search</span>
                            <font-awesome-icon aria-hidden="true"  icon="fa-regular fa-search"  size="lg" />

                        </button>

                        <button type="button" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            <span class="sr-only">View notifications</span>
                            <font-awesome-icon aria-hidden="true"  icon="fa-regular fa-bell"  size="lg" />

                        </button>


                        <!-- Profile dropdown -->
                        <Menu as="div" class="ml-3 relative">
                            <div>
                                <MenuButton class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <span class="sr-only">Open user menu</span>
                                    <img v-if="$page.props.auth.user.data['profile_url']"  class="h-8 w-8 rounded-full"
                                         :src="$page.props.auth.user.data['profile_url']??null"
                                         alt="" />

                                    <span class="flex-1 ml-3 mr-1  flex-col min-w-0 hidden lg:flex">
                                        <span class="text-gray-900 text-sm font-medium truncate">{{ $page.props.auth.user.name }}</span>
                                        <span class="text-gray-500 text-sm truncate">{{ $page.props.auth.user.username }}</span>
                                    </span>
                                    <ChevronDownIcon class="hidden flex-shrink-0 ml-1 h-5 w-5 text-gray-400 lg:block" aria-hidden="true" />

                                </MenuButton>
                            </div>
                            <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                <MenuItems class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-200 focus:outline-none">
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">View profile</a>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Settings</a>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Notifications</a>
                                        </MenuItem>
                                    </div>
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Get desktop app</a>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Support</a>
                                        </MenuItem>
                                    </div>
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <Link method="post"  :href="route('logout')" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Logout</Link>
                                        </MenuItem>
                                    </div>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                </div>
            </div>
            <main class="flex-1">


                <slot/>
            </main>
        </div>
    </div>
</template>

<script setup>
import {ref, watchEffect} from 'vue';
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
import { BellIcon,ChevronDownIcon,ClockIcon, HomeIcon, MenuAlt1Icon, ViewListIcon, XIcon } from '@heroicons/vue/outline'
import { ChevronRightIcon, DotsVerticalIcon, SearchIcon, SelectorIcon } from '@heroicons/vue/solid'
import { usePage,Link } from '@inertiajs/inertia-vue3';
import {useLayoutStore} from '@/Stores/layout.js';

const layout = useLayoutStore();


watchEffect(() => {
  //  if (usePage().props.value.language) {locale.language = usePage().props.value.language;}
  //  if (usePage().props.value.translations) {locale.translations = usePage().props.value.translations;}
    if (usePage().props.value.layout) {layout.navigation = usePage().props.value.layout.navigation??null;}
    if (usePage().props.value.organisation) {layout.organisation = usePage().props.value.organisation??null;}

//    if (usePage().props.value.currentModels) {layout.currentModels = usePage().props.value.currentModels;}

});
const navigation = [
    { name: 'Home', href: '#', icon: HomeIcon, current: true },
    { name: 'My tasks', href: '#', icon: ViewListIcon, current: false },
    { name: 'Recent', href: '#', icon: ClockIcon, current: false },
]
const teams = [
    { name: 'Engineering', href: '#', bgColorClass: 'bg-indigo-500' },
    { name: 'Human Resources', href: '#', bgColorClass: 'bg-green-500' },
    { name: 'Customer Success', href: '#', bgColorClass: 'bg-yellow-500' },
]


const sidebarOpen = ref(false)
</script>
