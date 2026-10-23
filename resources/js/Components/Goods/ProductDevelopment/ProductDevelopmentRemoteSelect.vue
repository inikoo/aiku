<!--
  - Author Louis Perez
  - Created on 15-10-2026-23h-10m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref } from "vue"
import axios from "axios"
import Select from "primevue/select"
import { routeType } from "@/types/route"

interface Option {
    value: number
    label: string
}

const props = defineProps<{
    searchRoute: routeType
    placeholder?: string
}>()

const model = defineModel<number | null>()

const emit = defineEmits<{
    picked: [option: Option]
}>()

function onChange(event: { value: number | null }) {
    const option = options.value.find((candidate) => candidate.value === event.value)
    if (option) {
        emit("picked", option)
    }
}

const options = ref<Option[]>([])
const loading = ref(false)
let searchTimer: ReturnType<typeof setTimeout> | undefined
let requestSequence = 0

async function load(query: string) {
    const sequence = ++requestSequence
    loading.value = true
    try {
        const { data } = await axios.get(route(props.searchRoute.name, props.searchRoute.parameters), { params: { q: query } })
        if (sequence !== requestSequence) {
            return
        }
        const selected = options.value.find((option) => option.value === model.value)
        options.value = selected && !data.some((option: Option) => option.value === selected.value) ? [selected, ...data] : data
    } finally {
        if (sequence === requestSequence) {
            loading.value = false
        }
    }
}

function onFilter(event: { value: string }) {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => load(event.value ?? ""), 250)
}

function onShow() {
    if (!options.value.length) {
        load("")
    }
}
</script>

<template>
    <Select
        v-model="model"
        :options="options"
        optionLabel="label"
        optionValue="value"
        filter
        :filterFields="['label']"
        :loading="loading"
        :placeholder="placeholder"
        showClear
        class="w-full"
        overlayClass="text-sm"
        @filter="onFilter"
        @show="onShow"
        @change="onChange"
    />
</template>
