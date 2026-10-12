<script setup lang="ts">
import ConfirmDialog from "primevue/confirmdialog"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationTriangle, faTimes, faCheck } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"

library.add(faExclamationTriangle, faTimes, faCheck)

defineProps<{
	group: string
}>()
</script>

<template>
	<ConfirmDialog :group="group" :style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }">
		<template #container="{ message, acceptCallback, rejectCallback }">
			<div class="rounded-lg bg-white p-5">
				<div class="flex items-center gap-2 font-semibold text-gray-900">
					<FontAwesomeIcon icon="fal fa-exclamation-triangle" class="text-orange-500" fixed-width aria-hidden="true" />
					{{ message.header }}
				</div>
				<p class="mt-3 text-sm text-gray-600">{{ message.message }}</p>
				<div class="mt-5 flex justify-end gap-2">
					<Button type="tertiary" :label="message.rejectProps?.label ?? ctrans('Cancel')" @click="rejectCallback">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="message.acceptProps?.label ?? ctrans('Confirm')" @click="acceptCallback">
						<template #icon>
							<FontAwesomeIcon
								:icon="message.acceptProps?.severity === 'danger' ? 'fal fa-exclamation-triangle' : 'fal fa-check'"
								:class="message.acceptProps?.severity === 'danger' ? 'text-amber-500' : 'text-green-600'"
								fixed-width
								aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</template>
	</ConfirmDialog>
</template>
