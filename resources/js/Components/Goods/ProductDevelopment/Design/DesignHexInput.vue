<!--
  - Author Louis Perez
  - Created on 18-10-2026-02h-10m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref, watch } from "vue"
import ColorPicker from "primevue/colorpicker"
import InputText from "primevue/inputtext"
import Popover from "primevue/popover"
import { ctrans } from "@/Composables/useTrans"

const model = defineModel<string>({ required: true })

const emit = defineEmits<{
    open: []
}>()

const popover = ref<InstanceType<typeof Popover> | null>(null)
const typed = ref(model.value.toUpperCase())

watch(model, (value) => typed.value = value.toUpperCase())

const normalise = (value: string) => {
    const hex = value.trim().replace(/^#/, "")
    return /^[0-9a-fA-F]{6}$/.test(hex) ? `#${hex.toUpperCase()}` : null
}

function onTyped(value: string | undefined) {
    typed.value = (value ?? "").toUpperCase()
    const hex = normalise(typed.value)
    if (hex && hex !== model.value.toUpperCase()) {
        model.value = hex
    }
}

function onBlur() {
    typed.value = model.value.toUpperCase()
}

const pickerValue = (value: string) => value.replace(/^#/, "")

function onPicked(value: string | undefined) {
    const hex = normalise(value ?? "")
    if (hex) {
        model.value = hex
    }
}

function toggle(event: Event) {
    emit("open")
    popover.value?.toggle(event)
}
</script>

<template>
    <div class="flex items-center gap-2">
        <button
            type="button"
            class="h-8 w-8 shrink-0 rounded-md border border-gray-300 shadow-inner transition-shadow hover:ring-2 hover:ring-[--app-accent-muted]"
            :style="{ backgroundColor: model }"
            :aria-label="ctrans('Choose a colour')"
            @click="toggle"
        />
        <InputText :modelValue="typed" size="small" maxlength="7" class="min-w-0 flex-1 font-mono uppercase" :aria-label="ctrans('Hex colour')" @update:modelValue="onTyped" @blur="onBlur" @keydown.enter="onBlur" />
        <Popover ref="popover">
            <ColorPicker :modelValue="pickerValue(model)" inline format="hex" @update:modelValue="onPicked" />
        </Popover>
    </div>
</template>
