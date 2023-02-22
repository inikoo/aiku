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
    faShoppingCart,
    faUser
} from "@/../private/pro-light-svg-icons";
import {faCheckCircle} from '@/../private/pro-solid-svg-icons';

import PageHeading from '@/Components/Headings/PageHeading.vue';
import DashboardNavigation from '@/Components/Navigation/DashboardNavigation.vue';

library.add(faIdCard, faUser, faCheckCircle, faFolderTree, faFolder, faCube, faShoppingCart, faFileInvoice);

const props = defineProps(['title', 'pageHead', 'shop', 'treeMaps']);


</script>


<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <div class="m-4">
        <DashboardNavigation v-for="(treeMap,idx) in treeMaps" :key="idx" :nodes="treeMap"/>
    </div>

</template>

