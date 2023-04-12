<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 12 Oct 2022 16:50:56 Central European Summer Time, Benalmádena, Malaga,Spain
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableShops from "@/Pages/Tables/TableShops.vue";

const props = defineProps <{
    pageHead: object
    title: string
    data:object
}>()

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableShops :data="data" />
</template>

