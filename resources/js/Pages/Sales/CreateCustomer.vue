<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 13 Mar 2023 21:41:41 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';

defineProps([ 'title','pageHead']);


</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>

</template>

