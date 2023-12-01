<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 02 Mar 2023 21:30:03 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"

defineProps(['title', 'pageHead', 'flatTreeMaps']);

import {library} from '@fortawesome/fontawesome-svg-core';
import {faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt} from '@fal/';

library.add(faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt);

</script>

<template layout="App">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>



</template>

