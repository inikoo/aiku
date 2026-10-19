<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHandHoldingUsd, faTimes } from "@fal"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

library.add(faHandHoldingUsd, faTimes)

export interface MoneyRequestRow {
	id: number
	reference: string
	client_code: string
	requested_at: string
	state: string
	state_label: string
	currency_code: string
	total: number
	paid: number
	outstanding: number
	notes: string | null
	lines: { kind: string; kind_label: string; reference: string | null; amount: number }[]
	cancel_route: routeType | null
	pay_route: routeType | null
}

defineProps<{
	data: object
	tab?: string
}>()

const emit = defineEmits<{
	(e: "pay", row: MoneyRequestRow): void
	(e: "cancel", row: MoneyRequestRow): void
}>()

const locale = useLocaleStore()
const money = (code: string, amount: number) => locale.currencyFormat(code, amount)

const stateClass: Record<string, string> = {
	requested: "bg-amber-50 text-amber-700",
	settled: "bg-green-50 text-green-700",
	cancelled: "bg-gray-100 text-gray-500",
}
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(requested_at)="{ item }">
			<span class="whitespace-nowrap">{{ useFormatTime(item.requested_at) }}</span>
		</template>

		<template #cell(reference)="{ item }">
			<span class="whitespace-nowrap font-medium text-gray-700">{{ item.reference }}</span>
		</template>

		<template #cell(client_code)="{ item }">
			<span class="font-semibold uppercase text-gray-700">{{ item.client_code }}</span>
		</template>

		<template #cell(lines)="{ item }">
			<div class="flex flex-wrap gap-x-3 gap-y-0.5 text-sm">
				<span v-for="(line, index) in item.lines" :key="index" class="whitespace-nowrap">
					<span class="text-gray-500">{{ line.kind_label }}</span>
					<span v-if="line.reference" class="ml-1 text-gray-700">{{ line.reference }}</span>
					<span class="ml-1 text-gray-400">{{ money(item.currency_code, line.amount) }}</span>
				</span>
			</div>
			<div v-if="item.notes" class="text-xs text-gray-400">{{ item.notes }}</div>
		</template>

		<template #cell(state)="{ item }">
			<span class="whitespace-nowrap rounded px-1.5 py-0.5 text-xs" :class="stateClass[item.state]">{{ item.state_label }}</span>
		</template>

		<template #cell(total)="{ item }">
			<span class="whitespace-nowrap">{{ money(item.currency_code, item.total) }}</span>
		</template>

		<template #cell(paid)="{ item }">
			<span class="whitespace-nowrap">{{ item.paid ? money(item.currency_code, item.paid) : "-" }}</span>
		</template>

		<template #cell(outstanding)="{ item }">
			<span class="whitespace-nowrap font-medium" :class="item.outstanding > 0 ? 'text-gray-800' : 'text-gray-400'">
				{{ item.outstanding > 0 ? money(item.currency_code, item.outstanding) : "-" }}
			</span>
		</template>

		<template #cell(actions)="{ item }">
			<div class="flex justify-end gap-2">
				<Button v-if="item.pay_route" type="tertiary" size="xs" :label="ctrans('Pay')" @click="emit('pay', item)">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-hand-holding-usd" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button v-if="item.cancel_route" type="tertiary" size="xs" :label="ctrans('Cancel')" @click="emit('cancel', item)">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</template>
	</Table>
</template>
