<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"
import type { ChecklistItem } from "@/Components/DataDisplay/ChecklistItems.vue"

defineProps<{
    checklist: {
        complete: number
        total: number
        items: ChecklistItem[]
        missing: string[]
    }
}>()
</script>

<template>
    <div class="min-w-[14rem] max-w-md">
        <div class="flex items-center gap-2">
            <div class="flex gap-px">
                <span
                    v-for="checklistItem in checklist.items"
                    :key="checklistItem.key"
                    v-tooltip="checklistItem.unchecked ? `${checklistItem.label} · ${ctrans('Not checked yet, needs a crawl')}` : checklistItem.label"
                    class="h-2.5 w-2 rounded-sm"
                    :class="checklistItem.done ? 'bg-green-500' : checklistItem.unchecked ? 'bg-gray-300' : 'bg-red-500'"
                />
            </div>
            <span
                class="text-sm font-semibold tabular-nums"
                :class="checklist.missing.length ? 'text-red-700' : 'text-green-700'"
            >
                {{ checklist.complete }}/{{ checklist.total }}
            </span>
        </div>
        <div v-if="checklist.missing.length" class="mt-0.5 text-xs font-medium text-red-700">
            {{ ctrans("Missing: :items", { items: checklist.missing.join(", ") }) }}
        </div>
        <div v-else class="mt-0.5 text-xs text-green-700">{{ ctrans("Complete") }}</div>
    </div>
</template>
