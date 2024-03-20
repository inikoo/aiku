<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 14 Sept 2022 15:29:08 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup>
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import FlatTreeMap from "@/Components/Navigation/FlatTreeMap.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt, faBars } from "@fal"
import { capitalize } from "@/Composables/capitalize"

defineProps(["title", "pageHead", "flatTreeMaps"])
library.add(faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt, faBars);

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <FlatTreeMap class="mx-4" v-for="(treeMap, idx) in flatTreeMaps" :key="idx" :nodes="treeMap" />
</template>
