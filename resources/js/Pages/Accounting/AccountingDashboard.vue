<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 01 Mar 2023 14:57:23 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import DashboardNavigation from '@/Components/Navigation/DashboardNavigation.vue';

defineProps(['title', 'pageHead', 'treeMaps']);

import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faCreditCard, faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar
} from "@/../private/pro-light-svg-icons";

library.add(faCreditCard, faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>

    <div class="m-4">
        <DashboardNavigation v-for="(treeMap,idx) in treeMaps" :key="idx" :nodes="treeMap"/>
    </div>

</template>

