<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 May 2023 09:18:00 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExclamationCircle, faCheckCircle } from '@fas'
import { library } from "@fortawesome/fontawesome-svg-core"
import { onMounted } from "vue"
import PureMultiselectInfiniteScroll from "@/Components/Pure/PureMultiselectInfiniteScroll.vue"
import { routeType } from "@/types/route"
library.add(faExclamationCircle, faCheckCircle)

const props = defineProps<{
    form: any
    fieldName: any
    options: string[] | {label?: string, value: string}[]
    fieldData: {
		fetchRoute: routeType
        placeholder?: string
        required?: boolean
        mode?: "multiple" | "single" | "tags"
		searchable?: boolean
        readonly?: boolean
		labelProp?: string
		valueProp?: string
    }
}>()

// Auto assign to first option if 'required' and value is null
onMounted(() => {
    if(props.fieldData?.required && !props.form[props.fieldName]) {
        props.form[props.fieldName] = props.options?.[0]?.value
    }
})
</script>

<template>
	<div class="">
		<div class="relative"
            :class="form.errors[fieldName] ? 'errorShake' : ''"
        >
			<PureMultiselectInfiniteScroll
				v-model="form[fieldName]"
                @update:modelValue="() => form.errors[fieldName] = null"
				:fetchRoute="fieldData.fetchRoute"
				:class="{ 'pr-8': form.errors[fieldName] || form.recentlySuccessful }"
				:initOptions="props.options"
				:placeholder="props.fieldData.placeholder ?? 'Select your option'"
				:canClear="!props.fieldData.required"
				:mode="props.fieldData.mode ? props.fieldData.mode : 'single'"
				:closeOnSelect="props.fieldData.mode == 'multiple' ? false : true"
				:canDeselect="!props.fieldData.required"
				:hideSelected="false"
                :disabled="fieldData.readonly"
                :caret="!fieldData.readonly"
				:labelProp="fieldData.labelProp || 'label'"
				:valueProp="fieldData.valueProp || 'value'"
			/>
			<div
				v-if="form.errors[fieldName] || form.recentlySuccessful"
				class="absolute inset-y-2/4 right-0 pr-3 flex items-center pointer-events-none bg-red-500">
				<FontAwesomeIcon
					icon="fas fa-exclamation-circle"
					v-if="form.errors[fieldName]"
					class="h-5 w-5 text-red-500"
					aria-hidden="true" />
				<FontAwesomeIcon
					icon="fas fa-check-circle"
					v-if="form.recentlySuccessful"
					class="mt-1.5 h-5 w-5 text-green-500"
					aria-hidden="true" />
			</div>
		</div>
        
		<p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">
			{{ form.errors[fieldName] }}
		</p>
	</div>
</template>
