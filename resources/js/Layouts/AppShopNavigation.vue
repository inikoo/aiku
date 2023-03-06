<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 19:45:30 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import { Link } from "@inertiajs/vue3";
import { router } from '@inertiajs/vue3'
import { trans } from "laravel-vue-i18n";
import DropDownShops from "@/Components/DropDownShops.vue";
import { ref } from "vue";
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

const props = defineProps(["shops"]);


let currentSlug = ref(props.shops.current.data.slug)

let currentName = ref(props.shops.current.data.name)
const handleShopChange = (shop) => {

    currentSlug.value = shop.slug
    currentName.value = shop.name
    let parameters = route().params;
    parameters['shop'] = shop.slug;
    router.get(route(route().current(), parameters))

}

</script>

<template>
    <div class="border border-sky-500 pl-6 pr-3">
        <FontAwesomeIcon aria-hidden="true"  icon="fal fa-store-alt" />

        <DropDownShops
            @change:shop="handleShopChange"
            :shops="shops"/>



    </div>
    <div class="ml-5 space-x-4">
        <Link :title="trans('products')"
              :href="shops.current?route('shops.show.products.index', currentSlug):route('products.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-cube" />
        </Link>
        <Link :title="trans('websites')"
              :href="shops.current?route('websites.index', currentSlug):route('websites.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-globe" />
        </Link>
        <Link :title="trans('customers')"
              :href="shops.current?route('shops.show.customers.index',currentSlug):route('customers.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-user" />
        </Link>
        <Link :title="trans('orders')"
              :href="shops.current?route('shops.show.orders.index', currentSlug):route('orders.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-shopping-cart" />
        </Link>
    </div>

</template>


