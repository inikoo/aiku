<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"
import ProductDevelopmentChecklist from "@/Components/Goods/ProductDevelopment/ProductDevelopmentChecklist.vue"

defineProps<{
    data: { code: string, name: string, checklist: any[] }[]
}>()
</script>

<template>
    <div v-if="data" class="grid gap-4 p-4 md:grid-cols-2 xl:grid-cols-3">
        <section v-for="sku in data" :key="sku.code" class="rounded-md p-3 ring-1 ring-gray-200">
            <h3 class="text-sm font-medium text-gray-800">{{ sku.code }} <span class="font-normal text-gray-500">{{ sku.name }}</span></h3>
            <ProductDevelopmentChecklist :rows="sku.checklist" class="mt-2" />
        </section>
        <p v-if="!data.length" class="text-sm text-gray-400">{{ ctrans("No SKUs in this development yet") }}</p>
    </div>
</template>
