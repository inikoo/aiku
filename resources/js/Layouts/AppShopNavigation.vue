<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 19:45:30 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import { Link, router } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
    faList, faFolderTree, faMailBulk
} from "@/../private/pro-light-svg-icons";
import { library } from "@fortawesome/fontawesome-svg-core";
import { useLayoutStore } from "@/Stores/layout";
import AppShopNavigationDropDown from "@/Layouts/AppShopNavigationDropDown.vue";
import { ref } from 'vue'

const layout = useLayoutStore();
library.add(faList, faFolderTree, faMailBulk);

const shopsFeatures = [


    {
        title: trans("Catalogue"),
        link1: "shops.show.catalogue.hub",
        link2: "catalogue.hub",
        icon : "fal fa-folder-tree"
    },
    {
        title: trans("Website"),
        link1: "shops.show.website",
        link2: "websites.index",
        icon : "fal fa-globe"
    },

    {
        title: trans("Customers"),
        link1: "shops.show.customers.index",
        link2: "customers.index",
        icon : "fal fa-user"
    },
    {
        title: trans("Orders"),
        link1: "shops.show.orders.index",
        link2: "orders.index",
        icon : "fal fa-shopping-cart"
    },
    {
        title: trans("Mailroom"),
        link1: "shops.show.mail.hub",
        link2: "mail.hub",
        icon : "fal fa-mail-bulk"
    },
    {
        title: trans("Accounting"),
        link1: "shops.show.accounting.dashboard",
        link2: "accounting.dashboard",
        icon : "fal fa-abacus"
    },
    {
        title: trans("Dispatch"),
        link1: "shops.show.dispatch.hub",
        link2: "dispatch.hub",
        icon : "fal fa-conveyor-belt-alt"
    }
];

const urlPage = ref()
router.on('navigate', (event) => {
    urlPage.value = location.href
    // console.log(location.href)
})

</script>

<template>
    <Link
        :class="'ml-8 hidden lg:block xl:ml-0 mr-4'"
        :title="trans('Shop')"
        :href="layout.currentShopSlug? route( 'shops.show',layout.currentShopSlug) : route('shops.index')">
        <font-awesome-icon
            aria-hidden="true"
            :icon="layout.currentShopSlug?'fal fa-store-alt':'fal fa-list'"
        />
    </Link>
    <div class="grid">
        <AppShopNavigationDropDown class="place-self-center" />
    </div>

    <!-- Icon Shops -->
    <div class="flex flex-wrap py-3 my-2 md:py-0 md:my-0 md:mt-0 md:inline-flex justify-start items-center border-b border-gray-200 md:border-0 bg-gray-100/50 md:bg-inherit md:space-y-0 md:space-x-0">
        <Link v-for="(shopsFeature, index) in shopsFeatures"
              :key="index"
              class="grid grid-flow-col grid-cols-7 justify-center items-center w-full py-1.5 px-4 space-x-0 group md:grid-cols-1 md:justify-end md:w-auto md:px-2 lg:px-4 "
              :title="trans(shopsFeature.title)"
              :href="layout.currentShopSlug? route(shopsFeature.link1, layout.currentShopSlug) : route(shopsFeature.link2)"
        >
            <div
                :class="{ 'border-b border-indigo-500 hover:border-indigo-500 text-indigo-500': route(shopsFeature.link2) == urlPage }"
                class="col-span-2 flex justify-center items-center text-gray-600 w-7 h-auto aspect-square hover:border-b hover:border-indigo-100"
            >
            <!-- {{ route(shopsFeature.link2) == urlPage }} -->
                <font-awesome-icon class="text-xs group-hover:text-indigo-500" aria-hidden="true" :icon="shopsFeature.icon" />
            </div>
            <div class="md:hidden col-span-5 ">
                <span class="text-xs inline text-gray-700 group-hover:text-indigo-500">{{ trans(shopsFeature.title) }}</span>
            </div>
        </Link>
    </div>
</template>


