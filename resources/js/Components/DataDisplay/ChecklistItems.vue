<script setup lang="ts">
import { computed } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faMinus } from "@fal"
import { ctrans } from "@/Composables/useTrans"

export type ChecklistItem = {
    key: string
    label: string
    done: boolean
    unchecked?: boolean
    missing_in?: string[]
}

const props = withDefaults(defineProps<{
    items: ChecklistItem[]
    variant?: "grid" | "list"
}>(), { variant: "grid" })

const missingFirst = computed(() => [...props.items].sort((first, second) => Number(first.done) - Number(second.done)))

const tooltipOf = (item: ChecklistItem) => {
    if (item.unchecked) {
        return ctrans("Not checked yet, needs a crawl")
    }

    return !item.done && item.missing_in?.length ? ctrans("Missing in :shops", { shops: item.missing_in.join(", ") }) : undefined
}

const iconOf = (item: ChecklistItem) => {
    if (item.done) {
        return { icon: faCheck, class: "text-green-600" }
    }

    return item.unchecked ? { icon: faMinus, class: "text-gray-400" } : { icon: faTimes, class: "text-red-600" }
}

const stateClass = (item: ChecklistItem) => {
    if (item.done) {
        return props.variant === "grid" ? "border-green-200 bg-green-50/60 text-gray-800" : "text-gray-700"
    }
    if (item.unchecked) {
        return props.variant === "grid" ? "border-gray-200 bg-gray-50 text-gray-500" : "bg-gray-50 text-gray-500"
    }

    return props.variant === "grid" ? "border-red-200 bg-red-50 font-medium text-gray-900" : "bg-red-50 font-medium text-gray-900"
}
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
            :class="[variant === 'grid' ? 'rounded-md border px-2.5 py-1.5' : 'rounded px-2 py-1', stateClass(item)]"
        >
            <FontAwesomeIcon :icon="iconOf(item).icon" :class="iconOf(item).class" fixed-width aria-hidden="true" />
            <span class="sr-only">{{ item.done ? ctrans("Met") : item.unchecked ? ctrans("Not checked yet") : ctrans("Not met") }}:</span>
            <span class="leading-tight">{{ item.label }}</span>
        </li>
    </ul>
</template>
