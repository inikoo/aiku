<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 04 Oct 2023 08:09:05 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import {faSign} from '@fal'
import {library} from "@fortawesome/fontawesome-svg-core";
import TableStockImages from "@/Components/Tables/TableStockImages.vue";
library.add(faSign)

const props = defineProps <{
    pageHead: object
    title: string
    data:object
}>()
</script>

<template layout="CustomerApp">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableStockImages :data="data" :is-select-image="false" />
</template>

