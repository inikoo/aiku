<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 13 Oct 2026, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationTriangle } from "@fas"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"

library.add(faExclamationTriangle)

export interface HandTypedCost {
	kind: string
	kind_label: string
	note: string | null
	amount: number
	is_estimate: boolean
}

const props = defineProps<{
	handTyped: HandTypedCost[]
	billed: Record<string, number | null>
	orgCurrency: string
}>()

const locale = useLocaleStore()
const money = (amount: number) => locale.currencyFormat(props.orgCurrency, amount)

const typed = (cost: HandTypedCost) =>
	cost.note
		? ctrans("Typed by hand: :typed (:note).", { typed: String(money(cost.amount)), note: cost.note })
		: ctrans("Typed by hand: :typed.", { typed: String(money(cost.amount)) })

const conflicts = computed(() =>
	props.handTyped
		.filter((cost) => cost.kind in props.billed)
		.map((cost) => {
			const invoiced = props.billed[cost.kind]
			const isReplaced = cost.kind === "shipping" || cost.kind === "duty"

			return { ...cost, invoiced, isReplaced, agrees: isReplaced && invoiced !== null && Math.abs(invoiced - cost.amount) < 0.005 }
		})
		.filter((cost) => !cost.agrees)
)

const warnings = computed(() => conflicts.value.filter((cost) => !(cost.isReplaced && cost.is_estimate)))
const estimates = computed(() => conflicts.value.filter((cost) => cost.isReplaced && cost.is_estimate))
</script>

<template>
	<div v-if="conflicts.length" class="space-y-2">
		<div v-if="warnings.length" role="alert" class="flex gap-3 rounded-md border-2 border-amber-400 bg-amber-50 p-3 text-amber-900">
			<FontAwesomeIcon icon="fas fa-exclamation-triangle" class="mt-0.5 text-2xl text-amber-500" aria-hidden="true" />
			<div class="space-y-2">
				<div v-for="cost in warnings" :key="`${cost.kind}-${cost.note}-${cost.amount}`">
					<template v-if="cost.isReplaced">
						<p class="text-base font-semibold">
							{{ cost.invoiced === null
								? ctrans(":cost is already typed by hand on this delivery", { cost: cost.kind_label })
								: ctrans(":cost was typed by hand and this invoice does not say the same", { cost: cost.kind_label }) }}
						</p>
						<p class="text-sm">
							{{ typed(cost) }}
							<template v-if="cost.invoiced !== null"> {{ ctrans("This invoice: :invoiced.", { invoiced: String(money(cost.invoiced)) }) }}</template>
							{{ ctrans("Adding the invoice replaces the amount typed by hand: check which one is right, and that no other invoice is missing.") }}
						</p>
					</template>
					<template v-else>
						<p class="text-base font-semibold">
							{{ ctrans(":cost is already typed by hand on this delivery", { cost: cost.kind_label }) }}
						</p>
						<p class="text-sm">
							{{ typed(cost) }}
							{{ ctrans("Adding the invoice counts both. If they are the same cost, remove the one typed by hand in Edit landed costs.") }}
						</p>
					</template>
				</div>
			</div>
		</div>
		<p v-for="cost in estimates" :key="`estimate-${cost.kind}`" class="text-sm text-gray-600">
			{{ ctrans("This invoice replaces the estimate of :typed typed for :cost.", { typed: String(money(cost.amount)), cost: cost.kind_label }) }}
		</p>
	</div>
</template>
