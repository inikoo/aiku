<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 22 Oct 2022 18:57:31 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->



<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale';

import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faDolly
} from '@fal';
import { capitalize } from "@/Composables/capitalize"
import TableOrgStockMovements from '@/Components/Tables/Grp/Org/Inventory/TableOrgStockMovements.vue'

library.add(
    faDolly
);

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()


</script>

  
  <template>
      <Head :title="capitalize(title)"/>
      <PageHeading :data="pageHead"></PageHeading>
      <TableOrgStockMovements :data="data" />
  </template>
  
  