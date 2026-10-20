<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { faArchive, faFileCsv, faFileCertificate } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"

library.add(faArchive, faFileCsv, faFileCertificate)

const props = defineProps<{
	data?: { id: number; scheme: string; scheme_label: string; from: string; to: string; kg: number; lines: number; notes: string | null; user: string | null; created_at: string }[]
	snapshotRoute: { name: string; parameters: Record<string, string> }
}>()

const locale = useLocaleStore()
const date = (iso: string) => new Date(iso.length === 10 ? `${iso}T00:00:00` : iso).toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" })
const url = (id: number, part?: string) => route(props.snapshotRoute.name, { ...props.snapshotRoute.parameters, eprSnapshot: id, ...(part ? { part } : {}) })
</script>

<template>
	<div class="px-4 py-5 space-y-5 text-sm text-gray-700">
		<div v-if="!data" class="h-40 rounded-lg bg-gray-100 animate-pulse" />

		<div v-else-if="!data.length" class="rounded-lg border border-dashed border-gray-300 px-6 py-10 text-center">
			<FontAwesomeIcon :icon="['fal', 'archive']" class="text-2xl text-gray-400" fixed-width aria-hidden="true" />
			<p class="mt-2 font-medium text-gray-900">{{ ctrans("No returns frozen yet") }}</p>
			<p class="mt-1 text-xs text-gray-500">{{ ctrans("Use Freeze for audit on the UK return or Scheme return tab when a return is submitted.") }}</p>
		</div>

		<div v-else class="rounded-lg ring-1 ring-gray-200 bg-white overflow-x-auto">
			<table class="min-w-full text-xs">
				<thead class="text-gray-600 whitespace-nowrap">
					<tr class="text-left">
						<th class="px-4 py-2 font-medium">{{ ctrans("Scheme") }}</th>
						<th class="px-2 py-2 font-medium">{{ ctrans("Period") }}</th>
						<th class="px-2 py-2 font-medium text-right">{{ ctrans("Total kg") }}</th>
						<th class="px-2 py-2 font-medium text-right">{{ ctrans("SKO lines") }}</th>
						<th class="px-2 py-2 font-medium">{{ ctrans("Frozen") }}</th>
						<th class="px-2 py-2 font-medium">{{ ctrans("Notes") }}</th>
						<th class="px-4 py-2" />
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-100">
					<tr v-for="snapshot in data" :key="snapshot.id">
						<td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">{{ snapshot.scheme_label }}</td>
						<td class="px-2 py-2 whitespace-nowrap">{{ date(snapshot.from) }} – {{ date(snapshot.to) }}</td>
						<td class="px-2 py-2 text-right tabular-nums">{{ locale.number(Math.round(snapshot.kg)) }}</td>
						<td class="px-2 py-2 text-right tabular-nums">{{ locale.number(snapshot.lines) }}</td>
						<td class="px-2 py-2 whitespace-nowrap">{{ date(snapshot.created_at) }}<span v-if="snapshot.user" class="text-gray-500"> · {{ snapshot.user }}</span></td>
						<td class="px-2 py-2 text-gray-500">{{ snapshot.notes }}</td>
						<td class="px-4 py-2">
							<div class="flex justify-end gap-2 whitespace-nowrap">
								<a :href="url(snapshot.id, 'return')">
									<Button type="tertiary" size="xs" :icon="['fal', 'file-certificate']" :label="ctrans('Return')" />
								</a>
								<a :href="url(snapshot.id)">
									<Button type="tertiary" size="xs" :icon="['fal', 'file-csv']" :label="ctrans('SKO lines')" />
								</a>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>
