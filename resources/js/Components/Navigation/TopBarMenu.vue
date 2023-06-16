<script setup lang="ts">

import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { trans } from 'laravel-vue-i18n';
import { useLayoutStore } from '@/Stores/layout.js';
import { router } from '@inertiajs/vue3';

const layout = useLayoutStore();

const props = defineProps<{
    currentPage: string
}>()

const isCurrentRoute = (slug: string) => {
    // To check if the value from first key (of params from URL) is same as slug from selected dropdown
    return route().params[Object.keys(route().params)[0]] == slug ? true : false
}


const handleClick = (option) => {
    layout.navigation[props.currentPage].currentData = option

    // if click 'All Inventories'
    if (option == null) {
        layout.navigation[props.currentPage].currentData = {
            slug: null,
            name: layout.navigation[props.currentPage].labelShowAll,
            code: trans('All')
        }

        // Redirect to warehouses
        return router.get(route(`${layout.navigation[props.currentPage].route}`))
    }

    // Redirect to warehouse
    router.get(route(`${layout.navigation[props.currentPage].routeOption}`, option.slug))
}


</script>

<template>
    <Menu as="div" class="ml-0 lg:ml-0 relative inline-flex text-right text-sm text-gray-800 w-40 lg:w-48">
        <!-- Box All Shops -->
        <MenuButton
            class="inline-flex place-self-center w-full justify-center gap-x-1.5 bg-white py-1 px-2 text-gray-800 border border-gray-300 hover:bg-gray-50">
            <span class="">
                {{ layout.navigation[props.currentPage].currentData.name }}
            </span>
            <FontAwesomeIcon aria-hidden="true" class="place-self-center ml-4 opacity-50 hover:opacity-100" icon="fal fa-chevron-down" />
        </MenuButton>

        <!-- Popup All Shops -->
        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems
                class="absolute w-max lg:w-56 divide-y divide-gray-300 top-8 right-0 z-10 mt-1 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                    <MenuItem v-slot="{ active }" v-for="option in layout.navigation[props.currentPage].topMenu.dropdown.options.data" :key="option.slug">
                        <button @click="handleClick(option)"
                            :class="[active ? 'bg-indigo-100 text-gray-900' : 'text-gray-700', isCurrentRoute(option.slug) ? 'font-semibold text-indigo-600' : '', 'w-full block px-4 py-2 text-sm']">
                            {{ option.name }}
                        </button>
                    </MenuItem>
                </div>
                <div class="py-1 ">
                    <MenuItem v-slot="{ active }">
                        <button @click="handleClick(null)"
                            :class="[active ? 'bg-indigo-100 text-gray-900' : 'text-gray-700', 'w-full block px-4 py-2 text-sm']">
                            {{ trans(layout.navigation[props.currentPage].labelShowAll) }}
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>


