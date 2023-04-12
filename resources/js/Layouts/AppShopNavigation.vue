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
        :class="'ml-8  xl:ml-0 mr-4'"
        :title="trans('Shop')"
          :href="layout.currentShopSlug? route( 'shops.show',layout.currentShopSlug) : route('shops.index')">
        <font-awesome-icon aria-hidden="true"
                           :icon="layout.currentShopSlug?'fal fa-store-alt':'fal fa-list'"
        />
    </Link>



    <AppShopNavigationDropDown/>

    <div class="ml-5 space-x-4">
        <Link :title="trans('Catalogue')"
              :href="layout.currentShopSlug? route( 'shops.show.catalogue.hub',layout.currentShopSlug) : route('catalogue.hub')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-folder-tree"/>
        </Link>
        <Link :title="trans('websites')"
              :href="layout.currentShopSlug?route('shops.show.websites.index', layout.currentShopSlug) : route('websites.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-globe"/>
        </Link>
        <Link :title="trans('customers')"
              :href="layout.currentShopSlug?route('shops.show.customers.index',layout.currentShopSlug):route('customers.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-user"/>
        </Link>
        <Link :title="trans('orders')"
              :href="layout.currentShopSlug?route('shops.show.orders.index', layout.currentShopSlug):route('orders.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-shopping-cart"/>
        </Link>
        <Link :title="trans('mailroom')"
              :href="layout.currentShopSlug?route('shops.show.mail.hub', layout.currentShopSlug):route('mail.hub')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-mail-bulk"/>
        </Link>

    </div>
</template>


