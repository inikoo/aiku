<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Fri, 17 Mar 2023 10:36:00 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import DashboardNavigation from '@/Components/Navigation/DashboardNavigation.vue';

defineProps(['title', 'pageHead', 'treeMaps']);

import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faCoins,
} from '@/../private/pro-light-svg-icons';

library.add(faCoins, faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>

    <div class="m-4">
        <DashboardNavigation v-for="(treeMap,idx) in treeMaps" :key="idx" :nodes="treeMap"/>
    </div>

</template>

