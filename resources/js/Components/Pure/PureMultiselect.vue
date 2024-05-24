<script setup lang='ts'>
import { ListboxLabel } from "@headlessui/vue";
import Multiselect from "@vueform/multiselect"

const props = defineProps<{
    modelValue: any
    placeholder?: string
    mode?: "single" | "multiple" | "tags"
    required?: boolean
    searchable?: boolean
    options: {
        label: string
    }[] | string[]
    caret?: boolean
    object?: boolean
    label?:String
    valueProp?:String
}>()


const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
    (e: 'OnChange', value: {}): void
}>()

const onInput = (keyOption : any) => {
    emits('update:modelValue', keyOption)
    emits('OnChange', keyOption)
}
    
</script>

<template>
    <!-- <pre>{{ options }}</pre> -->
    <div class="relative w-full text-gray-600">
        <Multiselect
            :value="modelValue"
            @input="onInput"
            :classes="{placeholder: 'text-sm text-left w-full pl-4 font-light text-gray-400'}"
            :options="props.options"
            :placeholder="placeholder ?? 'Select your option'"
            :canClear="!required"
            :mode="mode ? mode : 'single'"
            :closeOnSelect="mode == 'multiple' ? false : true"
            :canDeselect="!required"
            :hideSelected="false"
            :object="object"
            :searchable="!!searchable"
            :caret="caret ?? true"
            :label="label"
            :valueProp="valueProp"
        >
            <!-- <template #singlelabel :option="{ option }">{{option}}</template> -->
        </Multiselect>
    </div>
</template>

<style src="@vueform/multiselect/themes/default.css"></style>

<style>
/* Style for multiselect globally */
.multiselect-option.is-selected,
.multiselect-option.is-selected.is-pointed {
	@apply bg-gray-500 text-white;
}

.multiselect-option.is-selected.is-disabled {
	@apply bg-gray-200 text-white;
}

.multiselect.is-active {
	border: var(--ms-border-width-active, var(--ms-border-width, 1px)) solid
		var(--ms-border-color-active, var(--ms-border-color, #787878)) !important;
	box-shadow: 0 0 0 var(--ms-ring-width, 3px) var(--ms-ring-color, rgba(42, 42, 42, 0.188)) !important;
	/* box-shadow: 4px 0 0 0 calc(4px + 4px) rgba(42, 42, 42, 1); */
}

/* .multiselect-option.is-open {
	@apply outline-none border-none ring-transparent;
} */
</style>