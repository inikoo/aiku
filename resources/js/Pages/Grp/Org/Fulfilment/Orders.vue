<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup  lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableOrders from "@/Components/Tables/Grp/Org/Ordering/TableOrders.vue";
import { capitalize } from "@/Composables/capitalize"

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()

</script>

<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableOrders :data="data"  />
</template>
