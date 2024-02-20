
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 19 Feb 2024 21:18:50 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Stats from '@/Components/DataDisplay/Stats.vue';
import { capitalize } from "@/Composables/capitalize"

defineProps(['title', 'pageHead', 'stats']);

</script>

<template layout="Retina">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <stats class="m-4" :stats="stats"/>


</template>

