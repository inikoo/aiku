<script setup lang='ts'>
import Multiselect from "@vueform/multiselect"

const props = withDefaults(defineProps<{
    modelValue: any
    placeholder?: string
    mode?: "single" | "multiple" | "tags"
    required?: boolean
    searchable?: boolean
    clearOnBlur?: boolean  // Whether the search is cleared on blur or not
    clearOnSelect?: boolean
    classes?: {}
    options: {
        label: string
        value: string
    }[] | string[]
    caret?: boolean
    object?: boolean
    label?: string
    valueProp?: string
    isLoading?: boolean
    isError?: boolean
    resolveOnLoad?: boolean
    delay?: number
    minChars?: number
}>(), {
    clearOnBlur: true
})


const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
    (e: 'OnChange', value: {}): void
    (e: 'search-change', value: string): void
}>()

const onInput = (keyOption : any) => {
    emits('update:modelValue', keyOption)
    emits('OnChange', keyOption)
}
    
</script>

<template>
    <!-- <pre>{{ options }}</pre> -->
    <div class="relative w-full text-gray-600 rounded-sm">
        <Multiselect
            :value="modelValue"
            @input="onInput"
            @search-change="(e) => emits('search-change', e)"
            :loading="isLoading"
            :classes="{
                placeholder: 'pointer-events-none absolute top-1/2 z-10 -translate-y-1/2 select-none text-sm text-left w-full pl-4 font-light text-gray-400 opacity-1',
                ...classes,
            }"
            :options="props.options"
            :placeholder="placeholder ?? 'Select your option'"
            :canClear="!required"
            :mode="mode ? mode : 'single'"
            :closeOnSelect="mode == 'multiple' ? false : true"
            :canDeselect="!required"
            :hideSelected="false"
            :clearOnBlur
            :object
            :resolve-on-load
            :delay
            :clearOnSelect
            :min-chars
            :searchable="!!searchable"
            :caret="isLoading ? false : caret ?? true"
            :label="label"
            :valueProp="valueProp"
        >
            <template #singlelabel="{ value }">
                <slot name="label" :value />
            </template>

            <template #option="{option, isSelected, isPointed, search}">
                <slot name="option" :option :isSelected="isSelected(option)" :isPointed="isPointed(option)" :search />
            </template>

            <template #afterlist>
                <slot name="afterlist"></slot>
            </template>

            <template #spinner>
                <slot name="spinner"></slot>
            </template>
        </Multiselect>
    </div>
</template>

<style src="@vueform/multiselect/themes/default.css"></style>

<style>
.multiselect-single-label {
    padding-right: calc(1.5rem + var(--ms-px, .035rem)*3) !important;
}

.multiselect-search {
    background: transparent !important;
}

/* For Multiselect */
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

.multiselect-dropdown {
    max-height: 250px !important;
}
.multiselect-tags-search {
    @apply focus:outline-none focus:ring-0
}

.multiselect-tags {
    @apply m-0.5
}

.multiselect-tag-remove-icon {
    @apply text-lime-800
}
</style>