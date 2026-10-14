<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import Checkbox from "primevue/checkbox"
import Textarea from "primevue/textarea"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faThumbsDown, faTimes, faCheck } from "@fal"

library.add(faThumbsDown, faTimes, faCheck)

const props = defineProps<{
	orgStockId: number
	source: "buying" | "customer_services" | "goods_in"
	code?: string | null
	tally?: { times: number; people: number } | null
	label?: string
}>()

const reasonOptions = [
	{ value: "high_complaints", label: ctrans("High complaints") },
	{ value: "slow_seller", label: ctrans("Slow seller") },
	{ value: "high_competition", label: ctrans("High competition") },
	{ value: "import_issues", label: ctrans("Import issues") },
	{ value: "arrived_broken", label: ctrans("Arrived broken") },
	{ value: "arrived_spoiled", label: ctrans("Arrived rotten or out of date") },
]

const isOpen = ref(false)
const isSaving = ref(false)
const reasons = ref<string[]>([])
const note = ref("")

const canSend = computed(() => reasons.value.length > 0 && note.value.trim().length >= 3)

const send = () => {
	const sentFor = props.tally
	const timesBefore = sentFor?.times
	isSaving.value = true
	router.post(
		route("grp.models.org_stock.discontinue_suggestion.store", [props.orgStockId]),
		{ source: props.source, reasons: reasons.value, note: note.value },
		{
			preserveScroll: true,
			preserveState: true,
			onSuccess: () => {
				if (sentFor && sentFor.times === timesBefore) {
					sentFor.times++
					sentFor.people = Math.max(1, sentFor.people)
				}
				isOpen.value = false
				reasons.value = []
				note.value = ""
				notify({ title: ctrans("Suggestion recorded"), type: "success" })
			},
			onError: (errors) => notify({ title: ctrans("Something went wrong"), text: Object.values(errors)[0], type: "error" }),
			onFinish: () => (isSaving.value = false),
		}
	)
}
</script>

<template>
	<div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-0.5 text-xs">
		<button type="button" class="text-gray-400 hover:text-gray-700 hover:underline" @click.stop.prevent="isOpen = true">
			<FontAwesomeIcon icon="fal fa-thumbs-down" fixed-width aria-hidden="true" />
			{{ label ?? ctrans("Suggest discontinuing") }}
		</button>
		<span
			v-if="tally && tally.times > 0"
			v-tooltip="ctrans('How many times staff across the group suggested discontinuing this product. The Supply Chain Managers see this count.')"
			class="cursor-help rounded bg-amber-100 px-1.5 py-0.5 font-semibold text-amber-700">
			{{ ctrans("Suggested :times times by :people people", { times: String(tally.times), people: String(tally.people) }) }}
		</span>

		<Dialog
			v-model:visible="isOpen"
			modal
			dismissableMask
			:closable="false"
			:style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }"
			:pt="{ header: { class: '!px-4 !py-2.5 border-b border-gray-200' }, content: { class: '!p-4' } }">
			<template #header>
				<span class="text-sm font-semibold text-gray-900">{{ ctrans("Suggest discontinuing :code", { code: code ?? "" }) }}</span>
			</template>
			<div class="space-y-3 text-sm text-gray-700">
				<p class="text-xs text-gray-500">
					{{ ctrans("Nothing changes yet. Your report is added to the count the Supply Chain Managers see, and they act when enough people point at the same product.") }}
				</p>
				<div class="space-y-1.5">
					<div class="text-xs font-semibold text-gray-600">{{ ctrans("Why? Tick at least one") }}</div>
					<label v-for="option in reasonOptions" :key="option.value" class="flex cursor-pointer items-center gap-2">
						<Checkbox v-model="reasons" :value="option.value" size="small" />
						{{ option.label }}
					</label>
				</div>
				<div>
					<div class="mb-1 text-xs font-semibold text-gray-600">{{ ctrans("Explain, in your own words") }}</div>
					<Textarea v-model="note" rows="3" class="w-full text-sm" :maxlength="2000" :placeholder="ctrans('What have you seen with this product?')" />
				</div>
				<div class="flex justify-end gap-2">
					<Button type="tertiary" size="xs" :label="ctrans('Cancel')" @click="isOpen = false">
						<template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
					</Button>
					<Button type="tertiary" size="xs" :label="ctrans('Send suggestion')" :disabled="!canSend" :loading="isSaving" @click="send">
						<template #icon><FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" /></template>
					</Button>
				</div>
			</div>
		</Dialog>
	</div>
</template>
