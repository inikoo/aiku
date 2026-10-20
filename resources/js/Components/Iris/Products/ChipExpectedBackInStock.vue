<script setup lang="ts">
import { computed } from "vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"

const props = defineProps<{
    product: {
        is_back_in_stock?: boolean
        expected_back_in_stock_at?: string | null
        expected_back_in_stock_is_estimate?: boolean
    }
}>()

const emits = defineEmits<{
    (e: "toggle"): void
}>()

const expectedBackDate = computed(() =>
    props.product.expected_back_in_stock_at
        ? useFormatTime(props.product.expected_back_in_stock_at, { formatTime: "dd.MM.yyyy" })
        : null
)

const expectedBackLabel = computed(() =>
    expectedBackDate.value
        ? props.product.expected_back_in_stock_is_estimate
            ? ctrans("Estimated back on :date", { date: expectedBackDate.value })
            : ctrans("Expected Back on :date", { date: expectedBackDate.value })
        : null
)
</script>

<template>
    <button type="button"
        v-tooltip="expectedBackLabel"
        @click.prevent.stop="emits('toggle')"
        class="flex min-h-8 min-w-0 items-center rounded-full px-3 py-1 text-left text-xs leading-tight shadow-md transition"
        :class="product.is_back_in_stock ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
        {{ product.is_back_in_stock ? ctrans("Back in stock alert on") : expectedBackLabel ?? ctrans("Notify me when back in stock") }}
    </button>
</template>
