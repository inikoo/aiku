<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Fri, 24 Feb 2023 10:21:46 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';

defineProps(["title","pageHead","supplier"])

import {library} from '@fortawesome/fontawesome-svg-core';
import {faInventory,faWarehouse,faMapSigns} from '@/../private/pro-light-svg-icons';
library.add(faInventory,faWarehouse,faMapSigns);

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
</template>

