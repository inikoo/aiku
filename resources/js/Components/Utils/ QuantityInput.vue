<script setup lang="ts">
import { computed } from "vue"
import InputNumber from "primevue/inputnumber"
import InputGroup from "primevue/inputgroup"
import InputGroupAddon from "primevue/inputgroupaddon"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

const props = defineProps({
	data: {
		type: Object,
		required: true,
	},
	action: {
		type: Object,
		required: true,
	},
})

const emits = defineEmits(["update", "submit", "undo"])

const data = computed(() => props.data)

const triggerInput = () => {
	props.data.inputTriggered = true
	emitUpdate()
}

const onValueChange = () => {
	emitUpdate()
}

const onManualInputChange = (value: number) => {
	props.data.quantity_ordered = value
	emitUpdate()
}

const onUndo = () => {
	emits("undo", props.data.id)
}

const onSubmit = (type: string) => {
	emits("submit", { type, data: props.data })
}

const emitUpdate = () => {
	emits("update", props.data)
}
</script>

<template>
	<div>
		<div v-if="!data.inputTriggered" class="custom-input-number">
			<InputNumber
				v-model="data.quantity_ordered"
				inputClass="w-14 text-center"
				showButtons
				buttonLayout="horizontal"
				@keydown.enter="triggerInput"
				@change="onValueChange"
				@blur="triggerInput"
				:min="0">
				<template #incrementicon>
					<div
						class="flex items-center justify-center cursor-pointer"
						@click="onSubmit('increment')">
						<FontAwesomeIcon
							size="sm"
							icon="fas fa-plus"
							class="text-black"
							fixed-width
							aria-hidden="true" />
					</div>
				</template>
				<template #decrementicon>
					<div
						class="flex items-center justify-center cursor-pointer"
						@click="onSubmit('decrement')">
						<FontAwesomeIcon
							size="sm"
							icon="fas fa-minus"
							class="text-black"
							fixed-width
							aria-hidden="true" />
					</div>
				</template>
			</InputNumber>
		</div>
		<div v-else class="custom-input-number">
			<InputGroup>
				<InputGroupAddon @click="onUndo">
					<FontAwesomeIcon
						size="sm"
						icon="fas fa-undo"
						class="text-black"
						fixed-width
						aria-hidden="true" />
				</InputGroupAddon>
				<InputNumber
					v-model="data.quantity_ordered"
					buttonLayout="horizontal"
					:style="{ width: '49px' }"
					:min="0"
					@update:modelValue="onManualInputChange" />
				<InputGroupAddon @click="onSubmit('save')">
					<FontAwesomeIcon
						icon="fas fa-save"
						size="sm"
						class="text-black"
						fixed-width
						aria-hidden="true" />
				</InputGroupAddon>
			</InputGroup>
		</div>
	</div>
</template>

<style scoped>
.custom-input-number :deep(.p-inputnumber) {
	--p-inputnumber-button-width: 35px;
	height: 35px;
}
</style>
