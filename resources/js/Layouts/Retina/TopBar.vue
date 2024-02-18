<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 18 Feb 2024 06:36:19 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {  router, Link } from "@inertiajs/vue3"
import { useLayoutStore } from "@/Stores/layout"
import TopBarNavs from "@/Layouts/Retina/TopBarNavs.vue"
import { ref } from "vue"


import {
    Menu,
    MenuButton,
    MenuItem,
    MenuItems
} from "@headlessui/vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Disclosure } from "@headlessui/vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SearchBar from "@/Components/SearchBar.vue"
import { trans } from "laravel-vue-i18n"
import Image from "@/Components/Image.vue"

const props = defineProps<{
    modelValue: boolean
}>()

defineEmits<{
    (e: 'update:modelValue', sideBar: boolean): void
}>()

const layout = useLayoutStore()

const showSearchDialog = ref(false)

const logoutAuth = () => {
    router.post(route('retina.logout'))
}

</script>

<template>
    <Disclosure as="nav" class="fixed top-0 z-[21] w-full text-white" v-slot="{ open }">
        <div class="flex h-11 lg:h-10 flex-shrink-0">
            <div class="flex transition-all duration-300 ease-in-out">
                <!-- Hamburger -->
                <button class="block md:hidden w-10 h-10 relative focus:outline-none" @click="$emit('update:modelValue', !modelValue)">
                    <span class="sr-only">Open sidebar</span>
                    <div class="block w-5 absolute left-1/2 top-1/2 transform  -translate-x-1/2 -translate-y-1/2">
                        <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-100 transform transition duration-200 ease-in-out"
                            :class="{'rotate-45': modelValue,' -translate-y-1.5': !modelValue }"></span>
                        <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-100 transform transition duration-100 ease-in-out" :class="{'opacity-0': modelValue } "></span>
                        <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-100 transform transition duration-200 ease-in-out"
                            :class="{'-rotate-45': modelValue, ' translate-y-1.5': !modelValue}"></span>
                    </div>
                </button>

                <!-- App Title -->
                <div class="bg-gray-600 flex flex-1 items-center justify-center md:justify-start transition-all duration-300 ease-in-out border-b border-gray-500"
                    :class="[layout.leftSidebar.show ? 'md:w-56 md:pr-4' : 'md:w-10']"
                >
                    <Link :href="layout.app.url"
                            class="hidden md:flex flex-nowrap items-center h-full overflow-hidden gap-x-3 transition-all duration-200 ease-in-out"
                            :class="[layout.leftSidebar.show ? 'pl-2 py-1' : '']"
                        >
                        <Image :src="layout.app.logo" class="aspect-square h-full"/>
                        <p class="bg-gradient-to-r from-gray-100 to-gray-300 text-transparent text-lg bg-clip-text font-bold whitespace-nowrap leading-none lg:truncate">
                            {{ layout.app.name }}
                        </p>
                    </Link>
                </div>
            </div>

            <div class="bg-gray-50 flex items-center space-x-3 justify-end lg:justify-between w-full pl-3 pr-4 border-b border-gray-200">
                <!-- Section: Top menu -->
                <TopBarNavs />

                <!-- Section: Avatar Group -->
                <div class="flex gap-x-3">
                    <!-- <div class="cursor-pointer text-white bg-indigo-500 px-2 py-0.5 rounded-md select-none" @click="changeColorMode(true)">Dark mode: True</div>
                    <div class="cursor-pointer text-white bg-indigo-500 px-2 py-0.5 rounded-md select-none" @click="changeColorMode(false)">Dark mode: False</div>
                    <div class="cursor-pointer text-white bg-indigo-500 px-2 py-0.5 rounded-md select-none" @click="changeColorMode('system')">Dark mode: OS System</div> -->

                    <!-- Button: Search -->
                    <button @click="showSearchDialog = !showSearchDialog" id="search"
                            class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <span class="sr-only">{{ trans("Search") }}</span>
                        <FontAwesomeIcon aria-hidden="true" icon="far fa-search" size="lg" />
                        <SearchBar v-if="showSearchDialog" v-on:close="showSearchDialog = false" />
                    </button>

                    <!-- Button: Notifications -->
                    <!-- <button type="button"
                            class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <span class="sr-only">{{ trans("View notifications") }}</span>
                        <FontAwesomeIcon aria-hidden="true" icon="far fa-bell" size="lg" />
                    </button> -->

                    <!-- Avatar Button -->
                    <Menu as="div" class="relative">
                        <MenuButton id="avatar-thumbnail"
                            class="flex max-w-xs overflow-hidden items-center rounded-full bg-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <span class="sr-only">{{ trans("Open user menu") }}</span>
                            <Image class="h-8 w-8 rounded-full"
                                :src="layout.avatar_thumbnail"
                                alt="" />

                        </MenuButton>

                        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                            <MenuItems class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-200 focus:outline-none">
                                <div class="py-1">
                                    <MenuItem v-slot="{ active }">
                                        <div as="ul" type="button" @click="router.visit(route('retina.profile.show'))"
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
    </Disclosure>
</template>

