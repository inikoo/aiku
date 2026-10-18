<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Link, router } from "@inertiajs/vue3"
import Dialog from "primevue/dialog"
import Textarea from "primevue/textarea"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faTimes, faGavel } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import InformationIcon from "@/Components/Utils/InformationIcon.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"

library.add(faCheck, faTimes, faGavel)

type SupplierLink = { code: string; name: string; route: routeType | null }

export interface SupplierChangeRequestRow {
	id: number
	type: "deactivate" | "merge"
	type_label: string
	state: "pending" | "approved" | "rejected"
	state_label: string
	supplier: SupplierLink
	target: SupplierLink | null
	reason: string
	requested_by: string | null
	requested_at: string
	decided_by: string | null
	decided_at: string | null
	decision_note: string | null
	decide_route: routeType
}

const props = defineProps<{
	requests: SupplierChangeRequestRow[]
	canDecide: boolean
}>()

const href = (target: routeType) => route(target.name, target.parameters)
const pendingCount = computed(() => props.requests.filter((request) => request.state === "pending").length)

const deciding = ref<{ request: SupplierChangeRequestRow; approve: boolean } | null>(null)
const note = ref("")
const errors = ref<Record<string, string>>({})
const saving = ref(false)

const decisionVisible = computed({
	get: () => !!deciding.value,
	set: (value: boolean) => {
		if (!value) {
			deciding.value = null
		}
	},
})

function openDecision(request: SupplierChangeRequestRow, approve: boolean) {
	deciding.value = { request, approve }
	note.value = ""
	errors.value = {}
}

function decide() {
	if (!deciding.value) {
		return
	}
	router.patch(route(deciding.value.request.decide_route.name, deciding.value.request.decide_route.parameters), {
		approve: deciding.value.approve,
		decision_note: note.value.trim() || null,
	}, {
		only: ["dashboard"],
		preserveScroll: true,
		onStart: () => (saving.value = true),
		onFinish: () => (saving.value = false),
		onError: (error) => (errors.value = error),
		onSuccess: () => (deciding.value = null),
	})
}

const stateClasses: Record<string, string> = {
	pending: "bg-amber-50 text-amber-700",
	approved: "bg-green-50 text-green-700",
	rejected: "bg-gray-100 text-gray-600",
}
</script>

<template>
	<section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
		<header class="flex items-center gap-1.5 border-b border-gray-200 px-4 py-3">
			<FontAwesomeIcon icon="fal fa-gavel" class="text-gray-400" fixed-width aria-hidden="true" />
			<h2 class="text-sm font-semibold text-gray-700">{{ ctrans("Suggestions for the directors") }}</h2>
			<span v-if="pendingCount" class="rounded bg-amber-50 px-1.5 text-xs text-amber-700">{{ ctrans(":count waiting", { count: pendingCount }) }}</span>
			<InformationIcon :information="ctrans('Suppliers suggested for deactivation or merging. Nothing changes until a director approves; decisions from the last 90 days stay listed with who decided and why.')" class="text-xs text-gray-500" />
		</header>
		<p v-if="!requests.length" class="px-4 py-6 text-center text-sm text-gray-400">{{ ctrans("No suggestions yet") }}</p>
		<ul v-else class="divide-y divide-gray-100">
			<li v-for="request in requests" :key="request.id" class="px-4 py-3 text-sm">
				<div class="flex flex-wrap items-center justify-between gap-2">
					<span class="flex flex-wrap items-center gap-1.5">
						<span class="font-medium text-gray-700">{{ request.type_label }}</span>
						<Link v-if="request.supplier.route" :href="href(request.supplier.route)" class="font-medium text-gray-700 hover:underline">{{ request.supplier.code }}</Link>
						<span class="text-gray-500">{{ request.supplier.name }}</span>
						<template v-if="request.target">
							<span class="text-gray-400">→</span>
							<Link v-if="request.target.route" :href="href(request.target.route)" class="font-medium text-gray-700 hover:underline">{{ request.target.code }}</Link>
							<span class="text-gray-500">{{ request.target.name }}</span>
						</template>
					</span>
					<span class="rounded px-2 py-0.5 text-xs font-medium" :class="stateClasses[request.state]">{{ request.state_label }}</span>
				</div>
				<p class="mt-1 text-gray-600">{{ request.reason }}</p>
				<p class="mt-0.5 text-xs text-gray-400">{{ ctrans("Suggested by :user, :date", { user: request.requested_by ?? "", date: useFormatTime(request.requested_at) }) }}</p>
				<p v-if="request.decided_at" class="mt-0.5 text-xs text-gray-500">
					{{ ctrans("Decided by :user, :date", { user: request.decided_by ?? "", date: useFormatTime(request.decided_at) }) }}<span v-if="request.decision_note">: {{ request.decision_note }}</span>
				</p>
				<div v-if="canDecide && request.state === 'pending'" class="mt-2 flex gap-2">
					<Button type="tertiary" size="xs" :label="ctrans('Approve')" @click="openDecision(request, true)">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" size="xs" :label="ctrans('Keep the supplier')" @click="openDecision(request, false)">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</li>
		</ul>

		<Dialog v-model:visible="decisionVisible" modal :header="deciding?.approve ? ctrans('Approve: :type :code', { type: deciding.request.type_label, code: deciding.request.supplier.code }) : ctrans('Keep :code', { code: deciding?.request.supplier.code ?? '' })" :style="{ width: '30rem' }" :draggable="false">
			<div class="space-y-2 text-sm">
				<p v-if="deciding?.approve" class="text-gray-600">
					{{ deciding.request.type === "merge"
						? ctrans("Products, orders and containers move to :target and :code is archived.", { target: deciding.request.target?.code ?? "", code: deciding.request.supplier.code })
						: ctrans(":code is taken out of use in every organisation.", { code: deciding.request.supplier.code }) }}
				</p>
				<label class="flex flex-col gap-1">
					<span class="text-xs text-gray-500">{{ deciding?.approve ? ctrans("Note (optional)") : ctrans("Why the supplier is kept") }}</span>
					<Textarea v-model="note" rows="3" autoResize :invalid="!!(errors.decision_note || errors.approve)" />
					<span v-if="errors.decision_note || errors.approve || errors.supplier || errors.target_supplier_id" class="text-xs text-red-600">
						{{ errors.decision_note ?? errors.approve ?? errors.supplier ?? errors.target_supplier_id }}
					</span>
				</label>
			</div>
			<template #footer>
				<Button type="tertiary" :label="ctrans('Cancel')" @click="deciding = null">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" :label="deciding?.approve ? ctrans('Approve') : ctrans('Keep the supplier')" :loading="saving" :disabled="!deciding?.approve && note.trim().length < 3" @click="decide">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</template>
		</Dialog>
	</section>
</template>
