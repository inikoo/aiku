<script setup lang="ts">
import { computed } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes } from "@fal"
import { ctrans } from "@/Composables/useTrans"

export type ChecklistItem = {
    key: string
    label: string
    done: boolean
    missing_in?: string[]
}

const props = withDefaults(defineProps<{
    items: ChecklistItem[]
    variant?: "grid" | "list"
}>(), { variant: "grid" })

const missingFirst = computed(() => [...props.items].sort((first, second) => Number(first.done) - Number(second.done)))

const tooltipOf = (item: ChecklistItem) =>
    !item.done && item.missing_in?.length ? ctrans("Missing in :shops", { shops: item.missing_in.join(", ") }) : undefined
</script>

<template>
    <ul
        :class="variant === 'grid' ? 'grid grid-cols-2 gap-2 md:grid-cols-3 2xl:grid-cols-4' : 'space-y-1'"
    >
        <li
            v-for="item in missingFirst"
            :key="item.key"
            v-tooltip="tooltipOf(item)"
            class="flex items-center gap-2 text-sm"
            :class="[
                variant === 'grid' ? 'rounded-md border px-2.5 py-1.5' : 'rounded px-2 py-1',
                item.done
                    ? (variant === 'grid' ? 'border-green-200 bg-green-50/60 text-gray-800' : 'text-gray-700')
                    : (variant === 'grid' ? 'border-red-200 bg-red-50 font-medium text-gray-900' : 'bg-red-50 font-medium text-gray-900'),
            ]"
        >
            <FontAwesomeIcon
                :icon="item.done ? faCheck : faTimes"
                :class="item.done ? 'text-green-600' : 'text-red-600'"
                fixed-width
                aria-hidden="true"
            />
            <span class="leading-tight">{{ item.label }}</span>
        </li>
    </ul>
</template>
