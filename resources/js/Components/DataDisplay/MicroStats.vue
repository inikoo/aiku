<script setup lang="ts">
defineProps<{
    stats: { label: string, count: number, description?: string }[]
}>()
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <div v-for="stat in stats" :key="stat.label"
            class="flex items-center gap-3 rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs">
            <span class="text-gray-600">{{ stat.label }}</span>
            <span v-if="stat.description" class="text-gray-400">{{ stat.description }}</span>
            <span class="ml-auto font-semibold tabular-nums text-gray-800">{{ stat.count }}</span>
        </div>
    </div>
</template>
