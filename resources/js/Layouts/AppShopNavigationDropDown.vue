<script setup lang="ts">

import {Menu, MenuButton, MenuItem, MenuItems} from '@headlessui/vue';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {trans} from 'laravel-vue-i18n';
import {useLayoutStore} from '@/Stores/layout.js';
import {router} from '@inertiajs/vue3';

const layout = useLayoutStore();


const handleClick = (shopSlug) => {



    if (route().params.hasOwnProperty('shop')) {
        let routeName = route().current();
        let parameters;

        if (shopSlug) {

            parameters = {shop: shopSlug}

            if (routeName.startsWith('shops.show.customers')) {
                routeName = routeName.replace(/.customers.*/, '.customers.index');
            }else if (routeName.startsWith('shops.show.orders')) {
                routeName = routeName.replace(/.orders.*/, '.orders.index');
            }else if (routeName.startsWith('shops.show.catalogue')) {
                routeName = 'shops.show.catalogue.hub'
            } else if (routeName.startsWith('shops.show')) {
                routeName = 'shops.show'
            }
           // router.patch(route('sessions.current-shop.update', [shop.slug]));


        } else {
            parameters = {}
            if (routeName.startsWith('shops.show.customers')) {
                routeName = 'customers.index';
            } else if (routeName.startsWith('shops.show')) {
                routeName = 'shops.index'
            }
//        router.delete(route('sessions.current-shop.delete'));


        }

        router.get(route(routeName, parameters));


    }
    layout.currentShopSlug = shopSlug

}


</script>

<template>
    <Menu as="div" class="relative inline-block text-left    md:w-56">
        <MenuButton class="inline-flex w-full justify-center gap-x-1.5 bg-white px-3 py-1 text-sm  text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            <span class="hidden xl:inline">{{ layout.currentShopData.name }}</span> <span class="inline xl:hidden">{{ layout.currentShopData.code }}</span>
            <FontAwesomeIcon aria-hidden="true" class="ml-4 opacity-50 hover:opacity-100"
                             icon="fal fa-chevron-down"/>

        </MenuButton>
        <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">

            <MenuItems class="absolute w-56  divide-y divide-gray-300  right-0 z-10 mt-1  origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1 ">
                    <MenuItem v-slot="{ active }" v-for="shop in layout.shopsInDropDown" :key="shop.slug" :disabled="shop.slug===layout.currentShopSlug?true:null">
                        <button
                            @click="handleClick(shop.slug)"
                            :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'w-full block px-4 py-2 text-sm']">
                            {{ shop.name }}
                        </button>
                    </MenuItem>
                </div>
                <div class="py-1 ">
                    <MenuItem v-slot="{ active }">
                        <button
                            @click="handleClick(null)"
                            :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'w-full block px-4 py-2 text-sm']">
                            {{ trans('All Shops') }}
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>

</template>


