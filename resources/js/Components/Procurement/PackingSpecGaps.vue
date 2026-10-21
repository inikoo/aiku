<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faBoxOpen } from "@fal"
import { ctrans } from "@/Composables/useTrans"

defineProps<{
	gaps: {
		lines: { code: string; name: string | null; org_supplier_product_slug: string | null }[]
		cut_off: string | null
		blocking: boolean
		organisation_slug: string
	}
}>()

const formatDate = (iso: string) => new Date(iso).toLocaleDateString()
</script>

<template>
	<div class="rounded border px-3 py-2 text-sm" :class="gaps.blocking ? 'border-red-300 bg-red-50 text-red-800' : 'border-amber-300 bg-amber-50 text-amber-800'">
		<div class="flex items-center gap-2 font-medium">
			<FontAwesomeIcon :icon="faBoxOpen" fixed-width aria-hidden="true" />
			<template v-if="gaps.blocking">{{ ctrans("This order cannot be submitted: :count products have no packing spec from the agent.", { count: gaps.lines.length }) }}</template>
			<template v-else>{{ ctrans(":count products on this order have no packing spec yet.", { count: gaps.lines.length }) }}</template>
		</div>
		<div v-if="gaps.cut_off && !gaps.blocking" class="mt-0.5 text-xs">{{ ctrans("From :date the order will be blocked until they are declared.", { date: formatDate(gaps.cut_off) }) }}</div>
		<div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs">
			<template v-for="line in gaps.lines" :key="line.code">
				<Link
					v-if="line.org_supplier_product_slug"
					:href="route('grp.org.procurement.org_supplier_products.show', { organisation: gaps.organisation_slug, orgSupplierProduct: line.org_supplier_product_slug, tab: 'packing' })"
					class="whitespace-nowrap underline">
					{{ line.code }}
				</Link>
				<span v-else class="whitespace-nowrap">{{ line.code }}</span>
			</template>
		</div>
	</div>
</template>
