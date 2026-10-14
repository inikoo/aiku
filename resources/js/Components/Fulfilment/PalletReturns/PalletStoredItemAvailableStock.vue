<script setup lang="ts">
import { computed, inject } from 'vue'
import { ctrans } from '@/Composables/useTrans'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'

const props = defineProps<{
    quantityInPallet: number
    availableQuantity: number
    reservedQuantity: number
}>()

const emits = defineEmits<{
    (e: 'showReservations'): void
}>()

const locale = inject('locale', aikuLocaleStructure)

const hasReservations = computed(() => Number(props.reservedQuantity || 0) > 0)
</script>

<template>
    <span
        v-if="hasReservations"
        role="button"
        tabindex="0"
        @click="emits('showReservations')"
        @keydown.enter="emits('showReservations')"
        v-tooltip="ctrans(':available available out of :stock in this pallet. :reserved reserved by open returns. Click to see where.', {
            available: locale.number(availableQuantity || 0),
            stock: locale.number(quantityInPallet || 0),
            reserved: locale.number(reservedQuantity || 0)
        })"
        class="inline-block tabular-nums whitespace-nowrap cursor-pointer rounded px-1 hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
    >
        <span class="font-semibold" :class="Number(availableQuantity || 0) > 0 ? 'text-amber-600' : 'text-red-500'">{{ locale.number(availableQuantity || 0) }}</span>
        <span class="text-gray-400"> ({{ locale.number(quantityInPallet || 0) }})</span>
    </span>
    <span v-else class="tabular-nums">{{ locale.number(quantityInPallet || 0) }}</span>
</template>
