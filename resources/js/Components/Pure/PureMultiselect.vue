<script setup lang='ts'>
import Multiselect from "@vueform/multiselect"

const props = withDefaults(defineProps<{
    modelValue: any
    placeholder?: string
    mode?: "single" | "multiple" | "tags"
    required?: boolean
    searchable?: boolean
    clearOnBlur?: boolean  // Whether the search is cleared on blur or not
    classes?: {}
    options: {
        label: string
    }[] | string[]
    caret?: boolean
    object?: boolean
    label?: string
    valueProp?: string
}>(), {
    clearOnBlur: true
})


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
            :classes="{placeholder: 'pointer-events-none absolute top-1/2 z-10 -translate-y-1/2 select-none text-sm text-left w-full pl-4 font-light text-gray-400 opacity-1', ...classes}"
            :options="props.options"
            :placeholder="placeholder ?? 'Select your option'"
            :canClear="!required"
            :mode="mode ? mode : 'single'"
            :closeOnSelect="mode == 'multiple' ? false : true"
            :canDeselect="!required"
            :hideSelected="false"
            :clearOnBlur
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

.multiselect-single-label {
    padding-right: calc(0.25rem + var(--ms-px, .035rem)*3) !important;
}

/* .multiselect-option.is-open {
	@apply outline-none border-none ring-transparent;
} */
</style>