<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 22 Oct 2022 18:57:31 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->



<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faInventory,
    faBox,
    faClock,
    faCameraRetro,
    faPaperclip,
    faCube,
    faHandReceiving, faClipboard, faPoop, faScanner, faDollarSign
} from "@/../private/pro-light-svg-icons";
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Pages/ModelDetails.vue";
import TableSupplierProducts from "@/Pages/Tables/TableSupplierProducts.vue";
import TableProducts from "@/Pages/Tables/TableProducts.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableLocations from "@/Pages/Tables/TableLocations.vue";

library.add(
    faInventory,
    faBox,
    faClock,
    faCameraRetro,
    faPaperclip,
    faCube,
    faHandReceiving,
    faClipboard,
    faPoop,
    faScanner,
    faDollarSign,

);

const locale = useLocaleStore();

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    supplier_products: object;
    products: object
    locations: object;
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        locations: TableLocations,
        supplier_products: TableSupplierProducts,
        products: TableProducts,
        details: ModelDetails,
        history: ModelChangelog,
    };
    return components[currentTab.value];

});

</script>


<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <!--
    <div class="overflow-hidden bg-white shadow sm:rounded-md mx-5 max-w-lg  ">
        <div class="-ml-4 -mt-2 flex flex-wrap items-center justify-between sm:flex-nowrap px-6 py-4  border-b-2 border-grey-500">
            <div class="ml-4 mt-2">
            </div>
            <div class="ml-4 mt-2 flex-shrink-0 font-bold">
                {{locale.number(stock.data.quantity)}}
            </div>
        </div>
        <ul role="list" class="divide-y divide-gray-200 ">
            {{stock.locations}}
            <li v-for="location in stock.data.locations" :key="location.id">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="truncate text-sm font-medium text-indigo-600">{{ location.title }}</p>
                            <div class="ml-2 flex flex-shrink-0">
                                <p class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">{{ location.type }}</p>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    {{ location.department }}
                                </p>
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    {{ location.code }}
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    {{ locale.number(location.quantity) }}
                                    {{ ' ' }}
                                    <time :datetime="location.closeDate">{{ location.closeDateFull }}</time>
                                </p>
                            </div>
                        </div>
                    </div>

            </li>
        </ul>
    </div>
    -->
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>
