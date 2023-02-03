
<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sun, 30 Oct 2022 03:46:39 Greenwich Mean Time, Plane HK-KL
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faGlobe} from '@/../private/pro-light-svg-icons';
library.add(faGlobe);

const props = defineProps(['title', 'pageHead', 'parent']);


</script>


<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>


</template>

