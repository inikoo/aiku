<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import Textarea from "primevue/textarea"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faTimes } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"

library.add(faCheck, faTimes)

type SupplierOption = { id: number; code: string; name: string }

const props = defineProps<{
	suggestion: { type: "deactivate" | "merge"; supplier: SupplierOption; candidates?: SupplierOption[] } | null
}>()

const emit = defineEmits<{ (e: "close"): void }>()

const visible = computed({
	get: () => !!props.suggestion,
	set: (value: boolean) => {
		if (!value) {
			emit("close")
		}
	},
})

const reason = ref("")
const target = ref<number | null>(null)
const errors = ref<Record<string, string>>({})
const saving = ref(false)

watch(() => props.suggestion, () => {
	reason.value = ""
	target.value = props.suggestion?.candidates?.length === 1 ? props.suggestion.candidates[0].id : null
	errors.value = {}
})

const candidateOptions = computed(() => (props.suggestion?.candidates ?? []).map((candidate) => ({ value: candidate.id, label: `${candidate.code} · ${candidate.name}` })))

function submit() {
	if (!props.suggestion) {
		return
	}
	router.post(route("grp.models.supplier.change_request.store", { supplier: props.suggestion.supplier.id }), {
		type: props.suggestion.type,
		target_supplier_id: props.suggestion.type === "merge" ? target.value : null,
		reason: reason.value.trim(),
	}, {
		only: ["dashboard"],
		preserveScroll: true,
		onStart: () => (saving.value = true),
		onFinish: () => (saving.value = false),
		onError: (error) => (errors.value = error),
		onSuccess: () => emit("close"),
	})
}
</script>

<template>
	<Dialog
		v-model:visible="visible"
		modal
		:header="suggestion?.type === 'merge' ? ctrans('Suggest merging :code', { code: suggestion.supplier.code }) : ctrans('Suggest deactivating :code', { code: suggestion?.supplier.code ?? '' })"
		:style="{ width: '32rem' }"
		:draggable="false">
		<div v-if="suggestion" class="space-y-3 text-sm">
			<p class="text-gray-600">
				{{ suggestion.type === "merge"
					? ctrans("Its products, orders and containers move to the supplier kept, and this record is archived. The directors approve it first.")
					: ctrans("The supplier is taken out of use in every organisation; its history stays. The directors approve it first.") }}
			</p>
			<label v-if="suggestion.type === 'merge'" class="flex flex-col gap-1">
				<span class="text-xs text-gray-500">{{ ctrans("Merge into") }}</span>
				<Select v-model="target" :options="candidateOptions" optionLabel="label" optionValue="value" :placeholder="ctrans('Supplier to keep')" :invalid="!!errors.target_supplier_id" />
				<span v-if="errors.target_supplier_id" class="text-xs text-red-600">{{ errors.target_supplier_id }}</span>
			</label>
			<label class="flex flex-col gap-1">
				<span class="text-xs text-gray-500">{{ ctrans("Why") }}</span>
				<Textarea v-model="reason" rows="3" autoResize :invalid="!!errors.reason" />
				<span v-if="errors.reason || errors.type" class="text-xs text-red-600">{{ errors.reason ?? errors.type }}</span>
			</label>
		</div>
		<template #footer>
			<Button type="tertiary" :label="ctrans('Cancel')" @click="emit('close')">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="tertiary" :label="ctrans('Send to directors')" :loading="saving" :disabled="reason.trim().length < 5 || (suggestion?.type === 'merge' && !target)" @click="submit">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</Dialog>
</template>
