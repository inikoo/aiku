<script setup lang="ts">
import { ref, watch, defineEmits } from "vue"
import { trans } from "laravel-vue-i18n"
import SelectButton from "primevue/selectbutton"
import RadioButton from "primevue/radiobutton"
import PureInput from "@/Components/Pure/PureInput.vue"
import SelectQuery from "@/Components/SelectQuery.vue"

// Define props
const props = defineProps({
	modelValue: {
		type: Object,
		required: true,
	},
})

const emit = defineEmits(["update:modelValue"])
const options = ref([
	{ label: "Internal", value: "internal" },
	{ label: "External", value: "external" },
])
</script>

<template>
	<div v-if="modelValue.type">
		<div>
			<div class="text-gray-500 text-xs tracking-wide mb-2">{{ trans("Target") }}</div>
			<div class="mb-3 border border-gray-300 rounded-md w-full px-4 py-2">
				<!-- <SelectButton v-if="modelValue?.type"
					v-model="modelValue.type"
					:options="options"
					optionLabel="label"
					optionValue="value"
					:allowEmpty="false"
				>
					<template #option="slotProps">
						<span class="text-xs">{{ slotProps.option.label }}</span>
					</template>
				</SelectButton> -->

				<div class="flex flex-wrap justify-between w-full">
					<div v-for="(option, indexOption) in options" class="flex items-center gap-2">
						<RadioButton v-model="modelValue.type"
							:inputId="`${option.value}${indexOption}`"
							name="pizza"
							size="small"
							:value="option.value"
						/>
						<label @click="() => modelValue.type = option.value" :for="`${option.value}${indexOption}`" class="cursor-pointer">{{ option.label }}</label>
					</div>
				</div>
			</div>
		</div>
		
		<div>
			<div class="my-2 text-gray-500 text-xs tracking-wide mb-2">{{ trans("Destination") }}</div>
			<PureInput v-if="modelValue?.type == 'external'" v-model="modelValue.url" />
			<SelectQuery
				v-else-if="modelValue"
				fieldName="id"
				:object="true"
				:urlRoute="
					route('grp.org.shops.show.web.webpages.index', {
						organisation: route().params['organisation'],
						shop: route().params['shop'],
						website: route().params['website'],
					})
				"
				:value="modelValue"
				:closeOnSelect="true"
				label="url" />
		</div>
	</div>
</template>

<style lang="scss" scoped></style>
