<script setup lang="ts">
import { computed } from "vue"
import { ctrans } from "@/Composables/useTrans"

const props = defineProps<{
    data: {
        total: number
        states: Array<{
            state: string
            label: string
            count: number
            shops: string[]
        }>
    }
}>()

const stateColors: Record<string, string> = {
    in_process: "bg-lime-500 text-white",
    active: "bg-green-600 text-white",
    discontinuing: "bg-amber-400 text-amber-950",
    discontinued: "bg-gray-400 text-white",
    inactive: "bg-red-500 text-white",
}

const activeCount = computed(() => props.data.states.find(state => state.state === "active")?.count ?? 0)
</script>

<template>
    <div v-if="data.total" class="rounded-lg border border-gray-200 bg-white p-4">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h3 class="font-semibold text-gray-700">{{ ctrans("Status across :count shops", { count: data.total }) }}</h3>
            <span class="text-xs text-gray-500">{{ ctrans(":active of :total shops are active", { active: activeCount, total: data.total }) }}</span>
        </div>

        <div class="mt-3 flex h-4 gap-px overflow-hidden rounded">
            <div
                v-for="state in data.states"
                :key="state.state"
                v-tooltip="state.shops.join(', ')"
                :style="{ width: `${(state.count * 100) / data.total}%` }"
                :class="stateColors[state.state]"
                class="flex min-w-4 items-center justify-center text-[10px] font-semibold">
                {{ state.count }}
            </div>
        </div>

        <div class="mt-2 flex flex-wrap justify-between gap-x-8 gap-y-2">
            <div v-for="state in data.states" :key="state.state" class="flex min-w-0 gap-2">
                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-sm" :class="stateColors[state.state]" aria-hidden="true" />
                <div class="min-w-0">
                    <div class="text-xs font-semibold text-gray-700">{{ state.count }} · {{ state.label }}</div>
                    <div class="break-words text-[11px] text-gray-500">{{ state.shops.join(", ") }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
