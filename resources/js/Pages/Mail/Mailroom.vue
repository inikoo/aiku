<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 10:36:47 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faCashRegister,
    faCreditCard,
    faMoneyCheckAlt
} from "@/../private/pro-light-svg-icons";

import PageHeading from '@/Components/Headings/PageHeading.vue';
import FlatTreeMap from '@/Components/Navigation/FlatTreeMap.vue';

library.add(faCreditCard, faMoneyCheckAlt, faCashRegister);

const props = defineProps(['title', 'pageHead', 'mailroom', 'flatTreeMaps']);


</script>


<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
     <FlatTreeMap class="mx-4" v-for="(treeMap,idx) in flatTreeMaps" :key="idx" :nodes="treeMap"/>

</template>

