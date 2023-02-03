<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 22 Oct 2022 18:57:31 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->



<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';

defineProps(["title","pageHead","stock","locations"])

import {library} from '@fortawesome/fontawesome-svg-core';
import {faInventory,faBox} from '@/../private/pro-light-svg-icons';
library.add(faInventory,faBox);

const locale = useLocaleStore();


</script>


<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>


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


</template>
