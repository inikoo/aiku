
<script setup>
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt, faBars } from "@fal";
import { capitalize } from "@/Composables/capitalize";

defineProps(["title", "pageHead", "flatTreeMaps"]);
library.add(faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt, faBars);

</script>


<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
</template>

