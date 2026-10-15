<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"
import ProductDevelopmentProgressBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentProgressBar.vue"

defineProps<{
    roles: {
        role: string
        label: string
        done: number
        total: number
        missing: number
        starts_after_gate: boolean
    }[]
}>()
</script>

<template>
    <div class="grid grid-cols-2 gap-3 px-4 py-4 sm:grid-cols-3 md:px-6 lg:grid-cols-5">
        <div v-for="role in roles" :key="role.role" class="rounded-lg border border-gray-200 bg-white p-3">
            <div class="flex items-center justify-between gap-2">
                <span class="text-sm font-medium text-gray-800">{{ role.label }}</span>
                <span class="text-xs tabular-nums text-gray-500">{{ role.total ? role.done + "/" + role.total : "—" }}</span>
            </div>
            <ProductDevelopmentProgressBar :done="role.done" :total="role.total" class="mt-2" />
            <div class="mt-2 text-xs">
                <span v-if="role.starts_after_gate" class="text-gray-500">{{ ctrans("Starts after the gate") }}</span>
                <span v-else-if="!role.total" class="text-gray-400">{{ ctrans("Nothing to fill") }}</span>
                <span v-else-if="role.missing" class="text-amber-700">{{ ctrans(":count missing", { count: String(role.missing) }) }}</span>
                <span v-else class="text-green-700">{{ ctrans("All done") }}</span>
            </div>
        </div>
    </div>
</template>
