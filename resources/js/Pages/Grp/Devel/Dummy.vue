<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 11 May 2024 12:00:30 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
defineProps(['title','pageHead','stats'])

</script>

<template>
    <Head />
    <PageHeading :data="pageHead"></PageHeading>
</template>

