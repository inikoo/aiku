<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Apr 2024 18:08:36 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableRecurringBills from "@/Components/Tables/Grp/Org/Fulfilment/TableRecurringBills.vue";
import {capitalize} from "@/Composables/capitalize"
import {computed, ref} from "vue";
import {useTabChange} from "@/Composables/tab-change";
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";

const props = defineProps<{
  pageHead: object
  title: string
  data: {}
}>()

</script>
<template>
  <Head :title="capitalize(title)"/>
  <PageHeading :data="pageHead"></PageHeading>
  <TableRecurringBills :data="data" />
</template>

