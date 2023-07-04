<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 03 Jul 2023 16:55:06 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { Link } from "@inertiajs/vue3";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {trans} from 'laravel-vue-i18n';
import { library } from "@fortawesome/fontawesome-svg-core";
import {
    faStore
} from "../../../private/pro-light-svg-icons"
import { useLayoutStore } from "@/Stores/layout";

library.add(faStore);

const layout = useLayoutStore();

const helperMarketplaces = [
    {
        title: trans('Agents Marketplace'),
        label: trans('Agents'),
        route: 'procurement.marketplace.agents.index',
    },
    {
        title: trans('Supplier Marketplace'),
        label: trans('Supplier'),
        route: 'procurement.marketplace.suppliers.index',
    },
]

</script>

<template>
    <div  class="ml-4 flex items-center overflow-hidden">
        <div class="px-2 h-full flex justify-center items-center rounded-l border border-indigo-300">
            <FontAwesomeIcon icon="fal fa-store"
                             class="w-auto text-indigo-500"
                             aria-hidden="true" />
        </div>

        <div
            class="text-sm inline-flex place-self-center rounded-r justify-center border-solid border border-l-0 border-indigo-300">
            <Link v-for="helperMarketplace in helperMarketplaces" :href="route(helperMarketplace.route)"
                  :title="helperMarketplace.title"
                  class="group text-gray-400 flex justify-center items-center cursor-pointer h-7 py-1 space-x-1 px-3" :class="[
							[layout.currentRoute == helperMarketplace.route ? 'text-indigo-600' : 'hover:text-gray-600']
						]">
                <p class="hidden lg:inline capitalize">{{ helperMarketplace.label }}</p>
            </Link>
        </div>
    </div>

</template>

