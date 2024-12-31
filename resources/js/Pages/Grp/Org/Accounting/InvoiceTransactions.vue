<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { Invoice } from "@/types/invoice"
import { useLocaleStore } from "@/Stores/locale"
import { useFormatTime } from "@/Composables/useFormatTime"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faFileInvoiceDollar, faHandHoldingUsd } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
library.add(faFileInvoiceDollar, faHandHoldingUsd)

const props = defineProps<{
	data: {}
	tab?: string
}>()

const locale = useLocaleStore()

function invoiceRoute(invoice: Invoice) {}
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(reference)="{ item: invoice }">
			<Link :href="invoiceRoute(invoice)" class="primaryLink py-0.5">
				{{ invoice.reference }}
			</Link>
		</template>
	</Table>
</template>
