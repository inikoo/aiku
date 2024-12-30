<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import TableMailshots from "@/Components/Tables/TableMailshots.vue";
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faEnvelope } from "@fal";
library.add(
	faEnvelope
)
const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()
console.log(props);

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableMailshots :data="data"/>
</template>

