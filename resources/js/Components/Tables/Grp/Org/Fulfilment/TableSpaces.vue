<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Apr 2024 18:34:20 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { RecurringBill } from "@/types/recurring_bill"
import { Link } from "@inertiajs/vue3"
import { Pallet } from "@/types/Pallet"
import { faFileInvoiceDollar, faHandHoldingUsd } from "@fal"
import { useLocaleStore } from "@/Stores/locale"
import { library } from "@fortawesome/fontawesome-svg-core"
import { useFormatTime } from "@/Composables/useFormatTime"
library.add(faFileInvoiceDollar, faHandHoldingUsd)

const props = defineProps<{
	data: object
	tab?: string
}>()
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
	</Table>
</template>
