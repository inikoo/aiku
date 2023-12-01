
<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 18:41:25 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableWarehouses from "@/Components/Tables/TableWarehouses.vue";
import { capitalize } from "@/Composables/capitalize"

const props = defineProps<{
  pageHead: object
  title: string
  data: object
}>()
</script>

<!--suppress HtmlUnknownAttribute -->
<template layout="App">
    <!--suppress HtmlRequiredTitleElement -->
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableWarehouses :data="data" />
</template>

