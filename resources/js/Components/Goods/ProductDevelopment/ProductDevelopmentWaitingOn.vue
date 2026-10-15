<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import ProductDevelopmentSidePanel from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSidePanel.vue"

defineProps<{
    rows: {
        label: string
        role: string
        skus: string
        due_at: string | null
        is_late: boolean
    }[]
}>()

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null
</script>

<template>
    <ProductDevelopmentSidePanel :title="ctrans('Waiting on')">
        <ul class="divide-y divide-gray-100">
            <li v-for="(row, index) in rows" :key="index" class="flex items-start gap-2 px-4 py-2.5">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="row.is_late ? 'bg-red-500' : 'bg-amber-500'" aria-hidden="true" />
                <div class="min-w-0 flex-1">
                    <div class="text-sm text-gray-800">{{ row.label }}</div>
                    <div class="text-xs text-gray-500">{{ [row.role, row.skus].filter(Boolean).join(" · ") }}</div>
                </div>
                <span v-if="row.due_at" class="shrink-0 text-xs tabular-nums" :class="row.is_late ? 'text-red-600' : 'text-gray-500'">
                    {{ ctrans("Due :date", { date: formatDate(row.due_at) ?? "" }) }}
                </span>
            </li>
            <li v-if="!rows.length" class="px-4 py-4 text-sm text-gray-400">{{ ctrans("Nothing outstanding") }}</li>
        </ul>
    </ProductDevelopmentSidePanel>
</template>
