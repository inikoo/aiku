<script setup lang="ts">

import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { trans } from 'laravel-vue-i18n';
import { useLayoutStore } from '@/Stores/layout.js';
import { router } from '@inertiajs/vue3';
import { ref } from "vue";

const layout = useLayoutStore();

const props = defineProps<{
    currentPage: string
}>()

console.log(route().current()) // inventory.warehouses.show
console.log(layout[props.currentPage].routeSingle) // inventory.warehouses.show
console.log(route().params) // { warehouse: "ed" }

const isCurrentRoute = (slug: string) => {
    if (props.currentPage == 'inventory'){
        return route().params.warehouse == slug ? true : false 
    }
}

const handleClick = (option) => {
    layout[props.currentPage].currentData = option

    // if click 'All Inventories'
    if (option == null) {
        layout[props.currentPage].currentData = {
            slug: null,
            name: layout[props.currentPage].labelShowAll,
            code: trans('All')
        }

        // Redirect to warehouses
        return router.get(route(`${layout[props.currentPage].routeAll}`))
    }

    // Redirect to warehouse
    router.get(route(`${layout[props.currentPage].routeSingle}`, option.slug))
}


</script>

<template>
    <Menu as="div" class="ml-0 md:ml-8 lg:ml-0 relative inline-flex text-right w-10/12 md:w-44">
        <!-- Box All Shops -->
        <MenuButton
            class="inline-flex place-self-center w-full justify-center gap-x-1.5 bg-white py-1 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            <span class="">
                {{ layout[props.currentPage].currentData.name }}
                <!-- More -->
            </span>
            <!-- <span class="inline xl:hidden">{{ layout.currentShopData.code }}</span> -->
            <FontAwesomeIcon aria-hidden="true" class="place-self-center ml-4 opacity-50 hover:opacity-100" icon="fal fa-chevron-down" />
        </MenuButton>

        <!-- Popup All Shops -->
        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems
                class="absolute w-[134px] md:w-44 xl:w-56 divide-y divide-gray-300 top-8 right-0 z-10 mt-1 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
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
                            {{ trans(layout[props.currentPage].labelShowAll) }}
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>


