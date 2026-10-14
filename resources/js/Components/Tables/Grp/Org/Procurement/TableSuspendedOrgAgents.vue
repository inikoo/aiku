<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue"

defineProps<{
	data: object
	tab?: string
}>()

const orgAgentRoute = (orgAgent: { slug: string }) =>
	route("grp.org.procurement.org_agents.show", [route().params["organisation"], orgAgent.slug])
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(code)="{ item: orgAgent }">
			<Link :href="orgAgentRoute(orgAgent)" class="primaryLink">
				{{ orgAgent.code }}
			</Link>
		</template>
		<template #cell(location)="{ item: orgAgent }">
			<AddressLocation :data="orgAgent.location" />
		</template>
	</Table>
</template>
