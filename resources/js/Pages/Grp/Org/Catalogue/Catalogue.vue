<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 13 Oct 2022 15:35:22 Central European Summer Plane Malaga - East Midlands UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
  faCube,
  faFileInvoice,
  faFolder,
  faFolderTree,
  faChartLine,
  faShoppingCart, faStickyNote,faMoneyBillWave
} from "@fal";
import { faCheckCircle } from "@fas";

import PageHeading from "@/Components/Headings/PageHeading.vue";
import { capitalize } from "@/Composables/capitalize";

library.add(faChartLine, faCheckCircle, faFolderTree, faFolder, faCube, faShoppingCart, faFileInvoice, faStickyNote,
  faMoneyBillWave
);

const props = defineProps<{
  pageHead: {}
  tabs: {
    current: string
    navigation: {}
  },
  title: string
  dashboard?: {}


}>();


</script>


<template>

  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead" />

</template>
