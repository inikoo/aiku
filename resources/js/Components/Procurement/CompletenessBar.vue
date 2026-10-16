<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
defineProps<{
    value: number | null
}>()
</script>

<template>
    <span v-if="value !== null" class="inline-flex items-center justify-end gap-2" :aria-label="`${value}%`">
        <span class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-100">
            <span class="block h-full rounded-full" :class="value >= 100 ? 'bg-green-500' : value >= 50 ? 'bg-amber-400' : 'bg-red-400'" :style="{ width: `${value}%` }" />
        </span>
        <span class="w-9 text-right tabular-nums">{{ value }}%</span>
    </span>
    <span v-else class="text-gray-400">-</span>
</template>
