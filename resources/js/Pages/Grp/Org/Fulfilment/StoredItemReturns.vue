<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Jan 2024 15:13:18 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

  <script setup  lang="ts">
  import {Head} from '@inertiajs/vue3';
  import PageHeading from '@/Components/Headings/PageHeading.vue';
  import { capitalize } from "@/Composables/capitalize"
  import TablePalletDeliveries from "@/Components/Tables/Grp/Org/Fulfilment/TablePalletDeliveries.vue";
  import TableStoredItemReturn from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemReturn.vue";

  const props = defineProps<{
      data: object
      title: string
      pageHead: object
  }>()

  </script>

  <template>
      <Head :title="capitalize(title)"/>
      <PageHeading :data="pageHead"></PageHeading>
      <TableStoredItemReturn :data="data" />
  </template>
