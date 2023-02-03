<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Tue, 18 Oct 2022 09:24:28 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import DashboardNavigation from '@/Components/Navigation/DashboardNavigation.vue';

defineProps(['title', 'pageHead', 'treeMaps']);

import {library} from '@fortawesome/fontawesome-svg-core';
import {faFlask} from '@/../private/pro-light-svg-icons';

library.add(faFlask);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>

    <div class="m-4">
        <DashboardNavigation v-for="(treeMap,idx) in treeMaps" :key="idx" :nodes="treeMap"/>
    </div>

</template>

