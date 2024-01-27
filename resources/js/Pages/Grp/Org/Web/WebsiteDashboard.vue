<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import FlatTreeMap from '@/Components/Navigation/FlatTreeMap.vue';
import { capitalize } from "@/Composables/capitalize"

defineProps(['title', 'pageHead', 'flatTreeMaps']);

import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faCoins,
} from '@fal';

library.add(faCoins, faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar);

</script>

<template layout="App">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <FlatTreeMap class="mx-4" v-for="(treeMap,idx) in flatTreeMaps" :key="idx" :nodes="treeMap"/>
</template>

