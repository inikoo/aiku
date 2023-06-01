

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 23 Feb 2023 14:32:57 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';

defineProps(['title', 'pageHead', 'flatTreeMaps']);

import { library } from '@fortawesome/fontawesome-svg-core';
import { faNarwhal, faUserTie } from '@/../private/pro-light-svg-icons';
import FlatTreeMap from "@/Components/Navigation/FlatTreeMap.vue";

library.add(
    faNarwhal,
    faUserTie
);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <FlatTreeMap class="mx-4" v-for="(treeMap,idx) in flatTreeMaps" :key="idx" :nodes="treeMap"/>
</template>
