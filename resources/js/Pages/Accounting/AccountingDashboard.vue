<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 01 Mar 2023 14:57:23 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import FlatTreeMap from '@/Components/Navigation/FlatTreeMap.vue';

defineProps(['title', 'pageHead', 'flatTreeMaps']);

import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faCoins,
} from '@/../private/pro-light-svg-icons';

library.add(faCoins, faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>

     <FlatTreeMap class="m-4" v-for="(treeMap,idx) in flatTreeMaps" :key="idx" :nodes="treeMap"/>

</template>

