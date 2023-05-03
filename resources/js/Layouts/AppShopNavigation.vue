<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 19:45:30 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Link} from '@inertiajs/vue3';
import {trans} from 'laravel-vue-i18n';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {
    faList,faFolderTree,faMailBulk
} from '@/../private/pro-light-svg-icons';
import {library} from '@fortawesome/fontawesome-svg-core';
import {useLayoutStore} from '@/Stores/layout';
import AppShopNavigationDropDown from '@/Layouts/AppShopNavigationDropDown.vue';



const layout = useLayoutStore();
library.add(faList,faFolderTree,faMailBulk);

</script>

<template>
    <Link
        :class="'ml-8 hidden lg:block xl:ml-0 mr-4'"
        :title="trans('Shop')"
          :href="layout.currentShopSlug? route( 'shops.show',layout.currentShopSlug) : route('shops.index')">
        <font-awesome-icon aria-hidden="true"
                           :icon="layout.currentShopSlug?'fal fa-store-alt':'fal fa-list'"
        />
    </Link>

    <AppShopNavigationDropDown/>

    <!-- Icon Shops -->
    <div class="flex flex-wrap mt-3 md:mt-0 md:inline-flex justify-center items-center border-b border-gray-200 md:border-0 bg-gray-100/50 md:bg-inherit">
        <Link class="px-4 py-2 md:px-3 md:py-0 lg:px-4 inline-flex items-center" :title="trans('Catalogue')"
              :href="layout.currentShopSlug? route('shops.show.catalogue.hub',layout.currentShopSlug) : route('catalogue.hub')">
            <font-awesome-icon class="text-xl text-gray-700" aria-hidden="true" icon="fal fa-folder-tree" />
            <!-- <span class="md:hidden text-xs inline text-gray-900">{{trans('Catalogue')}}</span> -->
          </Link>
        <Link class="px-4 py-2 md:px-3 md:py-0 lg:px-4 inline-flex items-center" :title="trans('websites')"
              :href="layout.currentShopSlug?route('shops.show.websites.index', layout.currentShopSlug) : route('websites.index')">
            <font-awesome-icon class="text-xl text-gray-700" aria-hidden="true" icon="fal fa-globe"/>
            <!-- <span class="md:hidden text-xs inline text-gray-900">{{trans('Websites')}}</span> -->
          </Link>
        <Link class="px-4 py-2 md:px-3 md:py-0 lg:px-4 inline-flex items-center" :title="trans('customers')"
              :href="layout.currentShopSlug?route('shops.show.customers.index',layout.currentShopSlug):route('customers.index')">
            <font-awesome-icon class="text-xl text-gray-700" aria-hidden="true" icon="fal fa-user"/>
            <!-- <span class="md:hidden text-xs inline text-gray-900">{{trans('Customers')}}</span> -->
          </Link>
        <Link class="px-4 py-2 md:px-3 md:py-0 lg:px-4 inline-flex items-center" :title="trans('orders')"
              :href="layout.currentShopSlug?route('shops.show.orders.index', layout.currentShopSlug):route('orders.index')">
            <font-awesome-icon class="text-xl text-gray-700" aria-hidden="true" icon="fal fa-shopping-cart"/>
            <!-- <span class="md:hidden text-xs inline text-gray-900">{{trans('Orders')}}</span> -->
          </Link>
        <Link class="px-4 py-2 md:px-3 md:py-0 lg:px-4 inline-flex items-center" :title="trans('mailroom')"
              :href="layout.currentShopSlug?route('shops.show.mail.hub', layout.currentShopSlug):route('mail.hub')">
            <font-awesome-icon class="text-xl text-gray-700" aria-hidden="true" icon="fal fa-mail-bulk"/>
            <!-- <span class="md:hidden text-xs inline text-gray-900">{{trans('Mailroom')}}</span> -->
          </Link>
        <Link class="px-4 py-2 md:px-3 md:py-0 lg:px-4 inline-flex items-center" :title="trans('accounting')"
              :href="layout.currentShopSlug?route('shops.show.accounting.dashboard', layout.currentShopSlug):route('accounting.dashboard')">
            <font-awesome-icon class="text-xl text-gray-700" aria-hidden="true" icon="fal fa-abacus"/>
            <!-- <span class="md:hidden text-xs inline text-gray-900">{{trans('Accounting')}}</span> -->
          </Link>
        <Link class="px-4 py-2 md:px-3 md:py-0 lg:px-4 inline-flex items-center" :title="trans('accounting')"
              :href="layout.currentShopSlug?route('dispatch.hub', layout.currentShopSlug):route('dispatch.hub')">
            <font-awesome-icon class="text-xl text-gray-700" aria-hidden="true" icon="fal fa-conveyor-belt-alt"/>
            <!-- <span class="md:hidden text-xs inline text-gray-900">{{trans('Accounting')}}</span> -->
          </Link>
    </div>
</template>


