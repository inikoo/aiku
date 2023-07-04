<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Jul 2023 11:56:51 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";

import { useLayoutStore } from "@/Stores/layout";
import { trans } from "laravel-vue-i18n";
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";
import { computed } from "vue";

library.add(
);

const layout = useLayoutStore();


const dropDownData = computed(() => {

    const scope = layout.navigation?.[layout.currentModule]?.topMenu.dropdown.scope;
    if (scope == "shops") {
        return {
            selectedOption: layout.currentShopData.slug,
            label: layout.currentShopData.name,
            allLabel: trans("All Shops"),
            options: layout.shopsInDropDown,
            handleClick: shopHandleClick

        };
    } else if (scope == "websites") {
        return {
            selectedOption: layout.currentWebsiteData.slug,
            label: layout.currentWebsiteData.name,
            allLabel: trans("All websites"),
            options: layout.websitesInDropDown,
            handleClick: websiteHandleClick

        };
    } else if (scope == "warehouses") {
        return {
            selectedOption: layout.currentWarehouseData.slug,
            label: layout.currentWarehouseData.name,
            allLabel: trans("All warehouses"),
            options: layout.warehousesInDropDown,
            handleClick: warehouseHandleClick

        };
    }
    return null;

});

const shopHandleClick = (option) => {
    layout.currentShopData = option;

    if (layout.currentRoute.startsWith("shops")) {

        if (layout.currentRoute.startsWith("shops.show")) {

            router.get(
                option.slug ?
                    route("shops.show", [option.slug]) :
                    route("shops.index", [option.slug])
            );
        } else if (layout.currentRoute.startsWith("shops.index")) {
            router.get(route("shops.show", [option.slug]));
        }

    }
};

const websiteHandleClick = (option) => {
    layout.currentShopData = option;

    if (layout.currentRoute.startsWith("websites")) {

        if (layout.currentRoute.startsWith("websites.show")) {

            router.get(
                option.slug ?
                    route("websites.show", [option.slug]) :
                    route("websites.index", [option.slug])
            );
        } else if (layout.currentRoute.startsWith("websites.index")) {
            router.get(route("websites.show", [option.slug]));
        }

    }
};

const warehouseHandleClick = (option) => {
    layout.currentShopData = option;

    console.log(layout.currentRoute);
    if (layout.currentRoute.startsWith("inventory")) {

        if (layout.currentRoute.startsWith("inventory.warehouses.show")) {

            router.get(
                option.slug ?
                    route("inventory.warehouses.show", [option.slug]) :
                    route("inventory.warehouses.index", [option.slug])
            );
        } else if (layout.currentRoute.startsWith("inventory.warehouses.index")) {
            router.get(route("inventory.warehouses.show", [option.slug]));
        } else {


            layout.currentWarehouseData = option.slug ? layout.warehouses[option.slug] :
                {
                    slug: null,
                    name: trans("All warehouses"),
                    code: trans("All")
                };
        }

    }
};


</script>

<template>

    <Menu v-if="dropDownData &&  Object.keys(dropDownData.options).length>1" as="div" class="ml-0 lg:ml-4 relative inline-flex text-right text-sm text-gray-800 w-12 min-w-max lg:w-48">

        <MenuButton
            class="inline-flex place-self-center w-full justify-center gap-x-1.5 bg-white py-1 px-3 text-gray-800 border border-gray-300 hover:bg-gray-50">
            <span>
                {{ dropDownData.label }}
            </span>
            <FontAwesomeIcon aria-hidden="true" class="place-self-center ml-4 opacity-50 hover:opacity-100" icon="fal fa-chevron-down" />
        </MenuButton>

        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems
                class="absolute w-max lg:w-56 divide-y h-auto max-h-96 overflow-y-auto custom-hide-scrollbar divide-gray-300 top-8 right-0 z-10 mt-1 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                    <MenuItem v-slot="{ active }" v-for="option in dropDownData.options" :key="option.slug">
                        <button @click="dropDownData.handleClick(option)"
                                :class="[active ? 'bg-indigo-100 text-gray-900' : 'text-gray-700', dropDownData.selectedOption===option.slug ? 'font-semibold text-indigo-600' : '', 'w-full block px-4 py-2 text-sm']">
                            {{ option.name }}
                        </button>
                    </MenuItem>
                </div>
                <div class="py-1 ">
                    <MenuItem v-slot="{ active }">
                        <button @click="dropDownData.handleClick({slug: null,code: trans('All') ,name:dropDownData.allLabel })"
                                :class="[active ? 'bg-indigo-100 text-gray-900' : 'text-gray-700', 'w-full block px-4 py-2 text-sm']">
                            {{ dropDownData.allLabel }}
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>

    </Menu>

</template>

