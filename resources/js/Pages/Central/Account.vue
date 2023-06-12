<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 02 Mar 2023 21:32:55 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"

defineProps(['title', 'pageHead', 'content', 'tabs']);

const stats = [
    {name: 'Total Subscribers', stat: '71,897'},
    {name: 'Avg. Open Rate', stat: '58.16%'},
    {name: 'Avg. Click Rate', stat: '24.57%'},
    {name: 'Total Subscribers', stat: '71,897'},
    {name: 'Avg. Open Rate', stat: '58.16%'},
    {name: 'Avg. Click Rate', stat: '24.57%'},
];

import {library} from '@fortawesome/fontawesome-svg-core';
import {faBriefcase, faIndent} from '@/../private/pro-light-svg-icons';
import Tabs from '@/Components/Navigation/Tabs.vue';

library.add(faBriefcase, faIndent);


</script>

<template layout="App">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>


    <tabs :tabs="tabs"></tabs>


    <div v-show="tabs.current==='index'" class="px-4">
        <div class="pb-10">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Marketing</h3>
            <dl class="mt-2 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div v-for="item in stats" :key="item.name" class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">{{ item.name }}</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ item.stat }}</dd>
                </div>
            </dl>
        </div>

        <div class="pb-10">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Accounting</h3>
            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div v-for="item in stats" :key="item.name" class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">{{ item.name }}</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ item.stat }}</dd>
                </div>
            </dl>
        </div>
    </div>


</template>

