<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 23:14:33 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/inertia-vue3';

import PageHeading from '@/Components/Headings/PageHeading.vue';


const props = defineProps(['title', 'pageHead', 'customer']);


</script>


<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>


</template>

