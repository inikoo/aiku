<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 19:45:30 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Link} from '@inertiajs/vue3';

import {trans} from 'laravel-vue-i18n';

const props = defineProps(['shopsData']);

</script>

<template>
    <div class="border border-sky-500 pl-6 pr-3">
        <font-awesome-icon aria-hidden="true" class="mr-3" icon="fal fa-store-alt"/>
        <span v-if="!shopsData">{{ trans('Stores') }}</span>
        <span v-else>{{ shopsData.current.data.name }}</span>
        <font-awesome-icon aria-hidden="true" class="ml-4 opacity-50 hover:opacity-100" icon="fal fa-chevron-down"/>
    </div>
    <div class="ml-5 space-x-4">
        <Link :href="shopsData?route('dashboard.show'):route('dashboard.show')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-cube"/>
        </Link>
        <Link :href="shopsData?route('dashboard.show'):route('dashboard.show')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-globe"/>
        </Link>
        <Link :title="trans('customers')" :href="shopsData?route('shops.show.customers.index',shopsData.current.data.slug):route('customers.index')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-user"/>
        </Link>
        <Link :href="shopsData?route('dashboard.show'):route('dashboard.show')">
            <font-awesome-icon aria-hidden="true" icon="fal fa-shopping-cart"/>
        </Link>


    </div>

</template>


