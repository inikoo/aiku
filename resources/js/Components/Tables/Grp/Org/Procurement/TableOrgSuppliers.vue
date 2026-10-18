<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 24 Jul 2024 00:46:50 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { ctrans } from "@/Composables/useTrans"
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue"

interface OrgSupplier {
	org_supplier_slug: string
	code: string
	name: string
	location: object
}

defineProps<{
	data: {}
	tab?: string
}>()

function orgSupplierRoute(orgSupplier: OrgSupplier) {
	return route("grp.org.procurement.org_suppliers.show", [
		route().params["organisation"],
		orgSupplier.org_supplier_slug,
	])
}
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(code)="{ item: orgSupplier }">
			<Link :href="orgSupplierRoute(orgSupplier)" class="primaryLink">
				{{ orgSupplier["code"] }}
			</Link>
				<span v-if="orgSupplier.open_incidents" class="ml-1 rounded-full bg-gray-800 px-1.5 py-0.5 text-xs font-medium text-white tabular-nums" :title="ctrans('Open incidents')">{{ orgSupplier.open_incidents }}</span>
		</template>
		<template #cell(location)="{ item: orgSupplier }">
			<AddressLocation :data="orgSupplier['location']" />
		</template>
	</Table>
</template>
