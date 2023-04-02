<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 07 Sept 2022 23:27:32 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import  PageHeading from '@/Components/Headings/PageHeading.vue'
import TableUsers from "@/Pages/Tables/TableUsers.vue";

const props = defineProps <{
    pageHead: object
    title: string
    data: object
}>()

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableUsers :data="data" />
</template>

