<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"
import ProductDevelopmentSkuChecklist from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSkuChecklist.vue"
import type { ChecklistEditorData } from "@/Components/Goods/ProductDevelopment/productDevelopmentChecklist"

defineProps<{
    data: ChecklistEditorData
}>()
</script>

<template>
    <div v-if="data" class="space-y-3 p-4">
        <ProductDevelopmentSkuChecklist
            v-for="sku in data.skus"
            :key="sku.id"
            :sku="sku"
            :rows="sku.rows"
            :updateRoute="data.routes.update"
            :countries="data.countries"
            :defaultOpen="data.skus.length === 1"
        />
        <p v-if="!data.skus.length" class="text-sm text-gray-400">{{ ctrans("No SKUs in this development yet") }}</p>
    </div>
</template>
