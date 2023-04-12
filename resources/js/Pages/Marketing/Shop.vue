<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 13 Oct 2022 15:35:22 Central European Summer Plane Malaga - East Midlands UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faCube,
    faFileInvoice,
    faFolder,
    faFolderTree,
    faIdCard,
    faShoppingCart, faStickyNote,
    faUser,
} from '@/../private/pro-light-svg-icons';
import {faCheckCircle} from '@/../private/pro-solid-svg-icons';

import PageHeading from '@/Components/Headings/PageHeading.vue';
import FlatTreeMap from '@/Components/Navigation/FlatTreeMap.vue';

library.add(faIdCard, faUser, faCheckCircle, faFolderTree, faFolder, faCube, faShoppingCart, faFileInvoice, faStickyNote);

const props = defineProps(['title', 'pageHead', 'shop', 'flatTreeMaps']);


</script>


<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <FlatTreeMap class="mx-4" v-for="(treeMap,idx) in flatTreeMaps" :key="idx" :nodes="treeMap"/>
</template>

