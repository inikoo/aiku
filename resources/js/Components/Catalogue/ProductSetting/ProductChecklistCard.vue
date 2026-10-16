<script setup lang="ts">
import { computed } from "vue"
import { ctrans } from "@/Composables/useTrans"
import ChecklistItems from "@/Components/DataDisplay/ChecklistItems.vue"
import type { ShopProductSetting } from "@/Components/Catalogue/ProductSetting/types"

const props = defineProps<{
    checklist: ShopProductSetting["checklist"]
    note?: string
}>()

const percent = computed(() => (props.checklist.total ? (props.checklist.complete / props.checklist.total) * 100 : 0))
</script>

<template>
    <div class="rounded-lg border border-[--app-accent] bg-white p-4">
        <h3 class="font-semibold text-gray-900">{{ ctrans("Product checklist") }}</h3>
        <div class="mt-1 flex items-baseline gap-1">
            <span class="text-2xl font-semibold tabular-nums text-gray-900">{{ checklist.complete }} / {{ checklist.total }}</span>
            <span class="text-sm text-gray-500">{{ ctrans("checks met") }}</span>
        </div>
        <div class="mb-3 mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
            <div class="h-full rounded-full bg-[--app-accent]" :style="{ width: `${percent}%` }" />
        </div>
        <p v-if="note" class="mb-3 text-xs text-gray-500">{{ note }}</p>
        <ChecklistItems :items="checklist.items" variant="list" />
    </div>
</template>
