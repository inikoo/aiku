<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 19:45:30 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import { Link } from "@inertiajs/vue3";

import { trans } from "laravel-vue-i18n";
import DropDownShops from "@/Components/DropDownShops.vue";

const props = defineProps(["shopsData"]);


</script>

<template>
    <div class="border border-sky-500 pl-6 pr-3">
        <font-awesome-icon aria-hidden="true" class="mr-3" icon="fal fa-store-alt" />
        <span v-if="!shopsData.current">{{ trans("Stores") }}</span>
        <span v-else>{{ shopsData.current.data.name }}</span>
        <DropDownShops></DropDownShops>
    </div><!--{{shopsData.items.data}}-->
    <div class="ml-5 space-x-4">
        <Link :title="trans('products')"
              :href="shopsData.current?route('shops.show.products.index', shopsData.current.data.slug):route('products.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-cube" />
        </Link>
        <Link :title="trans('websites')"
              :href="shopsData.current?route('websites.index', shopsData.current.data.slug):route('websites.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-globe" />
        </Link>
        <Link :title="trans('customers')"
              :href="shopsData.current?route('shops.show.customers.index',shopsData.current.data.slug):route('customers.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-user" />
        </Link>
        <Link :title="trans('orders')"
              :href="shopsData.current?route('shops.show.orders.index', shopsData.current.data.slug):route('orders.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-shopping-cart" />
        </Link>
    </div>

</template>


