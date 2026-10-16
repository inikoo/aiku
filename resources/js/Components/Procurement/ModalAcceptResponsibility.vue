<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import Textarea from "primevue/textarea"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationTriangle, faTimes, faTrashAlt } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

library.add(faExclamationTriangle, faTimes, faTrashAlt)

const props = defineProps<{
	header: string
	route: routeType
	phrase: string
	warnings: string[]
	data?: Record<string, unknown>
}>()

const visible = defineModel<boolean>("visible", { required: true })

const typed = ref("")
const reason = ref("")
const minReasonLength = 10
const errors = ref<Record<string, string>>({})
const isDeleting = ref(false)

const normalise = (text: string) => text.trim().replace(/\s+/g, " ").replace(/[.\s]+$/, "").toLowerCase()
const isAccepted = computed(() => reason.value.trim().length >= minReasonLength && normalise(typed.value) === normalise(props.phrase))

watch(visible, (isOpen) => {
	if (isOpen) {
		typed.value = ""
		reason.value = ""
		errors.value = {}
	}
})

const destroy = () => {
	router.delete(route(props.route.name, props.route.parameters), {
		data: { ...(props.data ?? {}), reason: reason.value, responsibility: typed.value },
		onStart: () => { isDeleting.value = true },
		onFinish: () => { isDeleting.value = false },
		onSuccess: () => { visible.value = false },
		onError: (failed) => { errors.value = failed },
	})
}
</script>

<template>
	<Dialog v-model:visible="visible" modal :header="header" :style="{ width: '34rem' }" :breakpoints="{ '640px': '95vw' }">
		<div class="flex flex-col gap-4 text-sm text-gray-700">
			<div class="flex gap-3 rounded-md border border-red-200 bg-red-50 p-3">
				<FontAwesomeIcon icon="fal fa-exclamation-triangle" class="mt-0.5 text-lg text-red-500" fixed-width aria-hidden="true" />
				<div class="flex flex-col gap-2">
					<p class="font-semibold text-red-700">{{ ctrans("This can not be undone from here.") }}</p>
					<p v-for="warning in warnings" :key="warning">{{ warning }}</p>
				</div>
			</div>

			<label class="flex flex-col gap-1.5">
				<span>{{ ctrans("Why are you deleting it? This is kept on record.") }}</span>
				<Textarea v-model="reason" rows="3" autoResize autofocus class="w-full" :invalid="!!errors.reason" />
			</label>

			<label class="flex flex-col gap-1.5">
				<span>
					{{ ctrans("You take responsibility for anything that goes wrong because of this. To go ahead, type") }}
					<span class="font-semibold whitespace-nowrap">{{ phrase }}</span>
				</span>
				<InputText v-model="typed" :placeholder="phrase" autocomplete="off" class="w-full" :invalid="!!errors.responsibility" />
			</label>

			<p v-for="(error, key) in errors" :key="key" class="text-red-600">{{ error }}</p>
		</div>

		<template #footer>
			<Button type="tertiary" :label="ctrans('Cancel')" @click="visible = false">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="tertiary" :label="ctrans('Delete')" :disabled="!isAccepted" :loading="isDeleting" @click="destroy">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-trash-alt" class="text-amber-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</Dialog>
</template>
