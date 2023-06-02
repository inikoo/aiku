<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Tue, 25 Oct 2022 12:21:09 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import FlatTreeMap from '@/Components/Navigation/FlatTreeMap.vue';

defineProps(['title', 'pageHead', 'flatTreeMaps']);

import {library} from '@fortawesome/fontawesome-svg-core';
import {faPeopleArrows, faBoxUsd, faPersonDolly, faTruck, faBoxFull, faStore, faBuilding} from '@/../private/pro-light-svg-icons';

library.add(faPeopleArrows, faBoxUsd, faPersonDolly, faTruck, faBoxFull, faStore, faBuilding);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <FlatTreeMap class="mx-4" v-for="(treeMap,idx) in flatTreeMaps" :key="idx" :nodes="treeMap"/>
</template>

