<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 19:45:30 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Link} from '@inertiajs/vue3';
import {router} from '@inertiajs/vue3';
import {trans} from 'laravel-vue-i18n';
import DropDownShops from '@/Components/DropDownShops.vue';
import {ref} from 'vue';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {
    faList,faFolderTree
} from '@/../private/pro-light-svg-icons';
import {library} from '@fortawesome/fontawesome-svg-core';


const props = defineProps(['shops']);

let currentSlug = ref(null);
let isShopSet = ref(false);
if (props.shops.current) {
    currentSlug = ref(props.shops.current.data.slug);
    isShopSet = ref(true);
}
library.add(faList,faFolderTree);

const handleShopChange = (shop) => {

    isShopSet.value = true;
    currentSlug.value = shop.slug;
    let parameters = route().params;
    parameters['shop'] = shop.slug;

    if (route().current('shops.show.*')) {
        router.get(route(route().current(), parameters));
    } else {

        const newRoute = 'shops.show.' + route().current();
        if (route().has(newRoute)) {
            router.get(route(newRoute, parameters));
        } else {
            router.patch(route('sessions.current-shop.update', [shop.slug]));
        }
    }

};

const handleShopsChange = () => {

    if (route().current('shops.show.*')) {
        const newRoute = route().current().substring(11);
        let parameters = route().params;
        delete parameters['shop'];
        router.get(route(newRoute, parameters));
    } else {
        router.delete(route('sessions.current-shop.delete'));
    }
    isShopSet.value = false;

};


</script>

<template>

    <Link
        :class="'ml-8  xl:ml-0 mr-4'"
        :title="trans('Shop')"
          :href="isShopSet? route( 'shops.show',currentSlug) : route('shops.index')">
        <font-awesome-icon aria-hidden="true"
                           :icon="isShopSet?'fal fa-store-alt':'fal fa-list'"
        />
    </Link>


    <DropDownShops
        @select:shop="handleShopChange"
        @select:shops="handleShopsChange"
        :shops="shops"/>

    <div class="ml-5 space-x-4">
        <Link :title="trans('Catalogue')"
              :href="isShopSet? route( 'shops.show.catalogue.hub',currentSlug) : route('catalogue.hub')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-folder-tree"/>
        </Link>
        <Link :title="trans('websites')"
              :href="isShopSet?route('shops.show.websites.index', currentSlug) : route('websites.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-globe"/>
        </Link>
        <Link :title="trans('customers')"
              :href="isShopSet?route('shops.show.customers.index',currentSlug):route('customers.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-user"/>
        </Link>
        <Link :title="trans('orders')"
              :href="isShopSet?route('shops.show.orders.index', currentSlug):route('orders.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-shopping-cart"/>
        </Link>

    </div>
</template>


