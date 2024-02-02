<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

  <script setup  lang="ts">
  import {Head} from '@inertiajs/vue3';
  import PageHeading from '@/Components/Headings/PageHeading.vue';
  import TableOrders from "@/Components/Tables/TableOrders.vue";
  import { capitalize } from "@/Composables/capitalize"
  import TableStoredItems from "@/Components/Tables/TableStoredItems.vue";

  const props = defineProps<{
      data: object
      title: string
      pageHead: object
      stored_items?: object
      history?: object
  }>()

  console.log('porps',props)
  </script>

  <template layout="App">
      <Head :title="capitalize(title)"/>
      <PageHeading :data="pageHead"></PageHeading>
<!--      <TableStoredItems :data="stored_items"/>-->
  </template>
