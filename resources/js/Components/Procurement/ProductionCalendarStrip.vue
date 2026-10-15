<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed } from "vue"
import { ctrans } from "@/Composables/useTrans"

const props = withDefaults(defineProps<{
    calendar: Record<string, string | null>
    interactive?: boolean
}>(), { interactive: true })

const emit = defineEmits<{ (e: "paint", month: number): void }>()

const currentMonth = new Date().getMonth() + 1

const marks = computed<Record<string, { label: string; short: string; class: string }>>(() => ({
    best_to_order: { label: ctrans("Best to order"), short: "✓", class: "bg-green-100 text-green-800 ring-green-600/30" },
    peak_or_slow: { label: ctrans("Peak or slow"), short: "~", class: "bg-amber-100 text-amber-800 ring-amber-600/30" },
    cannot_produce: { label: ctrans("Can't produce"), short: "✕", class: "bg-red-100 text-red-800 ring-red-600/30" },
}))

const months = computed(() =>
    Array.from({ length: 12 }, (_, index) => {
        const month = index + 1
        const mark = props.calendar?.[month] ? marks.value[props.calendar[month] as string] : null
        return {
            month,
            name: new Date(2000, index, 1).toLocaleString(undefined, { month: "short" }),
            mark,
        }
    })
)

const isInteractive = computed(() => props.interactive)
</script>

<template>
    <div>
        <div class="grid grid-cols-6 gap-1 sm:grid-cols-12">
            <button v-for="month in months" :key="month.month" type="button"
                class="flex flex-col items-center rounded py-1 text-xs ring-1 ring-inset"
                :class="[month.mark ? month.mark.class : 'bg-white text-gray-500 ring-gray-200', month.month === currentMonth ? 'outline outline-2 outline-offset-1 outline-gray-500' : '', isInteractive ? 'hover:brightness-95' : 'cursor-default']"
                :aria-label="`${month.name}: ${month.mark?.label ?? ctrans('Not set')}`"
                v-tooltip="month.mark?.label ?? ctrans('Not set')"
                @click="isInteractive && emit('paint', month.month)">
                <span>{{ month.name }}</span>
                <span class="h-4 font-semibold" aria-hidden="true">{{ month.mark?.short ?? "" }}</span>
            </button>
        </div>
        <div class="mt-1.5 flex flex-wrap gap-3 text-[11px] text-gray-500">
            <span v-for="(mark, key) in marks" :key="key" class="inline-flex items-center gap-1">
                <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded text-[10px] ring-1 ring-inset" :class="mark.class" aria-hidden="true">{{ mark.short }}</span>
                {{ mark.label }}
            </span>
        </div>
    </div>
</template>
