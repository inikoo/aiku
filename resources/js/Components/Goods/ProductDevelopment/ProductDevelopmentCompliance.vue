<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
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

const MISSING_PREVIEW = 4
const expanded = ref<Record<number, boolean>>({})

const role = computed(() => props.data?.skus.flatMap((sku) => sku.rows)[0]?.role ?? null)
</script>

<template>
    <div v-if="data" class="space-y-4">
        <ul class="space-y-2 md:hidden">
            <li v-for="sku in data.skus" :key="sku.id" class="rounded-lg border border-gray-200 bg-white px-4 py-3">
                <div class="flex items-baseline gap-2">
                    <span class="shrink-0 font-medium text-gray-900">{{ sku.code }}</span>
                    <span class="min-w-0 truncate text-sm text-gray-700">{{ sku.name }}</span>
                </div>
                <div class="mt-3">
                    <div class="mb-1 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ ctrans("Compliance") }}</span>
                        <span class="tabular-nums">{{ summary(sku).done }}/{{ summary(sku).total }}</span>
                    </div>
                    <ProductDevelopmentProgressBar :done="summary(sku).done" :total="summary(sku).total" />
                </div>
                <div class="mt-3 border-t border-gray-100 pt-2.5">
                    <template v-if="summary(sku).missing.length">
                        <div class="mb-1.5 text-xs text-gray-500">{{ ctrans("Still missing (:count)", { count: String(summary(sku).missing.length) }) }}</div>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-for="missing in (expanded[sku.id] ? summary(sku).missing : summary(sku).missing.slice(0, MISSING_PREVIEW))" :key="missing" class="rounded-md bg-amber-50 px-2 py-0.5 text-xs text-amber-800">{{ missing }}</span>
                            <button
                                v-if="summary(sku).missing.length > MISSING_PREVIEW"
                                type="button"
                                class="rounded-md px-2 py-0.5 text-xs font-medium text-[--app-accent] hover:bg-[--app-accent-soft]"
                                @click="expanded[sku.id] = !expanded[sku.id]"
                            >
                                {{ expanded[sku.id] ? ctrans("Show less") : ctrans("+:count more", { count: String(summary(sku).missing.length - MISSING_PREVIEW) }) }}
                            </button>
                        </div>
                    </template>
                    <span v-else class="inline-block rounded-md bg-green-50 px-2 py-0.5 text-xs text-green-700">{{ ctrans("Complete") }}</span>
                </div>
            </li>
            <li v-if="!data.skus.length" class="rounded-lg border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-400">{{ ctrans("No SKUs in this development yet") }}</li>
        </ul>
        <div class="hidden overflow-x-auto rounded-lg border border-gray-200 bg-white [scrollbar-width:thin] md:block">
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
