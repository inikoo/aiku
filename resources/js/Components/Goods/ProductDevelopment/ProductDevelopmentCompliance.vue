<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed } from "vue"
import { ctrans } from "@/Composables/useTrans"
import ProductDevelopmentProgressBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentProgressBar.vue"
import ProductDevelopmentRoleSpecs from "@/Components/Goods/ProductDevelopment/ProductDevelopmentRoleSpecs.vue"
import type { ChecklistEditorData, ChecklistSku } from "@/Components/Goods/ProductDevelopment/productDevelopmentChecklist"

const props = defineProps<{
    data: ChecklistEditorData
}>()

const summary = (sku: ChecklistSku) => {
    const required = sku.rows.filter((row) => row.required)
    const missing = required.filter((row) => row.state === "missing")

    return { done: required.length - missing.length, total: required.length, missing: missing.map((row) => row.label) }
}

const role = computed(() => props.data?.skus.flatMap((sku) => sku.rows)[0]?.role ?? null)
</script>

<template>
    <div v-if="data" class="space-y-4">
        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white [scrollbar-width:thin]">
            <table class="w-full min-w-[40rem] table-fixed text-sm">
                <thead class="border-b border-gray-200 text-left text-xs text-gray-500">
                    <tr>
                        <th class="w-40 px-4 py-2 font-medium">{{ ctrans("SKU") }}</th>
                        <th class="w-56 px-4 py-2 font-medium">{{ ctrans("Product") }}</th>
                        <th class="w-36 px-4 py-2 font-medium">{{ ctrans("Compliance") }}</th>
                        <th class="px-4 py-2 font-medium">{{ ctrans("Still missing") }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="sku in data.skus" :key="sku.id" class="align-top">
                        <td class="truncate px-4 py-3 font-medium text-gray-900" :title="sku.code">{{ sku.code }}</td>
                        <td class="truncate px-4 py-3 text-gray-800" :title="sku.name">{{ sku.name }}</td>
                        <td class="px-4 py-3">
                            <div class="flex w-28 items-center gap-2">
                                <ProductDevelopmentProgressBar :done="summary(sku).done" :total="summary(sku).total" />
                                <span class="text-xs tabular-nums text-gray-500">{{ summary(sku).done }}/{{ summary(sku).total }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="summary(sku).missing.length" class="inline-block break-words rounded-md bg-amber-50 px-2 py-1 text-xs text-amber-800">{{ summary(sku).missing.join(", ") }}</span>
                            <span v-else class="inline-block rounded-md bg-green-50 px-2 py-1 text-xs text-green-700">{{ ctrans("Complete") }}</span>
                        </td>
                    </tr>
                    <tr v-if="!data.skus.length">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">{{ ctrans("No SKUs in this development yet") }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ProductDevelopmentRoleSpecs v-if="role" :role="role" :skus="data.skus" :updateRoute="data.routes.update" :countries="data.countries"
            :options="data.options" />
    </div>
</template>
