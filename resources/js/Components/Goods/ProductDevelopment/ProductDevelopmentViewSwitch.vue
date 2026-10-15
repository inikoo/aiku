<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

interface ViewOption {
    key: string
    label: string
    icon: string
    route: routeType
    active: boolean
}

const props = defineProps<{
    options: ViewOption[]
}>()

const current = ref(props.options.find((option) => option.active)?.key ?? "list")

watch(current, (key) => {
    const option = props.options.find((candidate) => candidate.key === key)
    if (option && !option.active) {
        router.get(route(option.route.name, option.route.parameters))
    }
})
</script>

<template>
    <SegmentedToggle
        v-model="current"
        :options="options.map((option) => ({ label: option.label, value: option.key, icon: option.icon }))"
        :ariaLabel="ctrans('View')"
    />
</template>
