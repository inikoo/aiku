<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import TableStructure from "@/Components/Tables/Grp/Org/Web/TableStructure.vue"
import { capitalize } from "@/Composables/capitalize"
import {
	faShapes,
	faSortAmountDownAlt,
	faBrowser,
	faSortAmountDown,
	faHome,
	faSignInAlt,
	faBooks,
	faColumns,
	faInfoCircle,
	faNewspaper,
} from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { PageHeading as TSPageHeading } from "@/types/PageHeading"
library.add(
	faShapes,
	faSortAmountDownAlt,
	faBrowser,
	faSortAmountDown,
	faHome,
	faSignInAlt,
	faBooks,
	faColumns,
	faInfoCircle,
	faNewspaper
)

const props = defineProps<{
	pageHead: TSPageHeading
	title: string
	data: {}
}>()
</script>

<template>
      <Head :title="capitalize(title)" />
      <PageHeading :data="pageHead" />
      <TableStructure  />
</template>
