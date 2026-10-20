<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faArchive, faCheck, faTimes } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"

library.add(faArchive, faCheck, faTimes)

const props = defineProps<{
	organisationId: number
	scheme: string
	schemeLabel: string
	period: { from: string; to: string }
	ownBrandImports?: boolean
}>()

const open = ref(false)
const saving = ref(false)
const notes = ref("")
const errors = ref<Record<string, string>>({})

const freeze = () => {
	saving.value = true
	router.post(
		route("grp.models.epr_snapshot.store", { organisation: props.organisationId }),
		{ scheme: props.scheme, date_from: props.period.from, date_to: props.period.to, own_brand_imports: !!props.ownBrandImports, notes: notes.value || null },
		{
			preserveScroll: true,
			onSuccess: () => {
				open.value = false
				notes.value = ""
			},
			onError: (e) => (errors.value = e),
			onFinish: () => (saving.value = false),
		}
	)
}

const date = (iso: string) => new Date(`${iso}T00:00:00`).toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" })
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] focus:!border-[--app-accent]"
</script>

<template>
	<div>
		<Button type="tertiary" :icon="['fal', 'archive']" :label="ctrans('Freeze for audit')" @click="(errors = {}), (open = true)" />

		<Dialog v-model:visible="open" modal :header="ctrans('Freeze this return for audit')" class="w-full max-w-md">
			<div class="space-y-3 text-sm text-gray-700">
				<p>
					{{ ctrans(":scheme, :from to :to. The figures and every SKO behind them, with the packaging weights and RAM evidence they carry now, are kept for seven years and cannot be changed.", { scheme: schemeLabel, from: date(period.from), to: date(period.to) }) }}
				</p>
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Notes (e.g. submitted to the scheme on…)") }}</span>
					<InputText v-model="notes" class="w-full !py-1 !text-xs" :class="fieldFocusClass" />
				</label>
				<div v-if="Object.keys(errors).length" class="text-xs text-red-600">{{ Object.values(errors).join(" ") }}</div>
				<div class="flex justify-end gap-2">
					<Button type="tertiary" :label="ctrans('Cancel')" @click="open = false">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="ctrans('Freeze')" :loading="saving" @click="freeze">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</Dialog>
	</div>
</template>
