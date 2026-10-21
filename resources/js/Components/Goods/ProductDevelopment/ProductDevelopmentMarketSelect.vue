<!--
  - Author Louis Perez
  - Created on 17-10-2026-18h-40m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed } from "vue"
import MultiSelect from "primevue/multiselect"
import Checkbox from "primevue/checkbox"
import { ctrans } from "@/Composables/useTrans"

const props = defineProps<{
    options: { value: string | number, label: string }[]
    placeholder?: string
    invalid?: boolean
}>()

const model = defineModel<(string | number)[]>({ default: () => [] })

const allSelected = computed(() => props.options.length > 0 && props.options.every((option) => model.value.includes(option.value)))
const someSelected = computed(() => !allSelected.value && model.value.length > 0)

function toggleAll() {
    model.value = allSelected.value ? [] : props.options.map((option) => option.value)
}
</script>

<template>
    <MultiSelect
        v-model="model"
        :options="options"
        optionLabel="label"
        optionValue="value"
        display="comma"
        :showToggleAll="false"
        :placeholder="placeholder"
        :invalid="invalid"
        class="w-full"
        overlayClass="text-sm"
    >
        <template #header>
            <button type="button" class="flex w-full items-center gap-2 border-b border-gray-100 px-3 py-2.5 text-left hover:bg-gray-50" @click="toggleAll">
                <Checkbox :modelValue="allSelected" :indeterminate="someSelected" binary readonly class="pointer-events-none" />
                <span class="font-semibold text-gray-900">{{ ctrans("Select all") }}</span>
            </button>
        </template>
    </MultiSelect>
</template>
