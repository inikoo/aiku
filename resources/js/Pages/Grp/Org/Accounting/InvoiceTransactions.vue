<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { Invoice } from "@/types/invoice"
import { useLocaleStore } from "@/Stores/locale"
import { useFormatTime } from "@/Composables/useFormatTime"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { capitalize } from "@/Composables/capitalize";
import { faExchangeAlt } from "@fal";

library.add(faExchangeAlt)

const props = defineProps<{
	data: {}
    title: string;
    pageHead: any;
}>()

const locale = useLocaleStore()

function invoiceRoute(invoice: Invoice) {}
</script>

<template>
      <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead" />

	<Table :resource="data"  class="mt-5">
		<template #cell(reference)="{ item: invoice }">
			<Link :href="invoiceRoute(invoice)" class="primaryLink py-0.5">
				{{ invoice.reference }}
			</Link>
		</template>
	</Table>
</template>
