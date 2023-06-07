<script setup lang="ts">

import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { trans } from 'laravel-vue-i18n';
import { useLayoutStore } from '@/Stores/layout.js';
import { router } from '@inertiajs/vue3';
import { ref } from "vue";

const layout = useLayoutStore();

const currentWarehouse = ref(layout.currentShopData);

const handleClick = (warehouseSlug) => {
    console.log(layout);
    let routeName = route().current();
    let parameters = route().params;

    if (warehouseSlug) {

        if (route().params.hasOwnProperty('warehouse')) {
            parameters = { warehouse: warehouseSlug }
            if (routeName.startsWith('c.locations')) {
                routeName = 'warehouses.show.warehouse-areas.show.locations.index'
            } else if (routeName.startsWith('warehouses.show.warehouse-areas')) {
                routeName = 'warehouses.show.warehouse-areas.index'
            } else if (routeName.startsWith('warehouses.show')) {
                routeName = 'warehouses.show'
            }
        } else {
            if (routeName.startsWith('warehouse-areas')) {
                parameters = { warehouse: warehouseSlug }
                routeName = 'warehouses.show.warehouse-areas.index'
            } else if (routeName.startsWith('catalogue.hub')) {
                parameters = { warehouse: warehouseSlug }
                routeName = 'shops.show.catalogue.hub'
            }

        }
        // router.patch(route('sessions.current-shop.update', [shop.slug]));


    } else {

        if (route().params.hasOwnProperty('warehouse')) {

            parameters = {}
            if (routeName.startsWith('shops.show.customers')) {
                routeName = 'customers.index';
            } else {
                routeName = 'shops.index'
            }
        }

        //        router.delete(route('sessions.current-shop.delete'));
    }

    router.get(route(routeName, parameters));

    // layout.currentWarehouseSlug = warehouseSlug
    // layout.currentWarehouseData = layout.warehouses[layout.currentWarehouseSlug] ?? {
    //     slug: null,
    //     name: trans('All Warehouse'),
    //     code: trans('All'),
    // };
    //
    // currentWarehouse.value = layout.currentWarehouseData;
}


</script>

<template>
    <Menu as="div" class="ml-0 md:ml-8 lg:ml-0 relative inline-flex text-right w-10/12 md:w-44">
        <!-- Box All Warehouse -->
        <MenuButton
            class="inline-flex place-self-center w-full justify-center gap-x-1.5 bg-white py-1 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            <span class="">{{ layout.currentShopData.name }}</span>
            <!-- <span class="inline xl:hidden">{{ layout.currentShopData.code }}</span> -->
            <FontAwesomeIcon aria-hidden="true" class="place-self-center ml-4 opacity-50 hover:opacity-100"
                icon="fal fa-chevron-down" />
        </MenuButton>

        <!-- Popup All Shops -->
        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems
                class="absolute w-[134px] md:w-44 xl:w-56 divide-y divide-gray-300 top-8 right-0 z-10 mt-1 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                    <MenuItem v-slot="{ active }" v-for="shop in layout.shopsInDropDown" :key="shop.slug">
                    <button @click="handleClick(shop.slug)"
                        :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'w-full block px-4 py-2 text-sm']">
                        {{ shop.name }}
                    </button>
                    </MenuItem>
                </div>
                <div class="py-1 ">
                    <MenuItem v-slot="{ active }">
                    <button @click="handleClick(null)"
                        :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'w-full block px-4 py-2 text-sm']">
                        {{ trans('All Shops') }}
                    </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>


