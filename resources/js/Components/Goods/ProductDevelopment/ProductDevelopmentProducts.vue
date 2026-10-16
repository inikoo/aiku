<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref } from "vue"
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import ProductDevelopmentProgressBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentProgressBar.vue"
import ProductDevelopmentSkuChecklist from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSkuChecklist.vue"
import ProductDevelopmentSupplierProductLink from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSupplierProductLink.vue"
import type { ChecklistEditorData } from "@/Components/Goods/ProductDevelopment/productDevelopmentChecklist"

interface Tally {
    done: number
    total: number
    missing: number
}

defineProps<{
    data: {
        rows: {
        id: number
        code: string
        name: string
        route: routeType
        supplier_product: string | null
        specs: Tally
        compliance: Tally
        label_status: string
        still_missing: string[]
        }[]
        editor: ChecklistEditorData
    }
}>()

const linkDialog = ref<InstanceType<typeof ProductDevelopmentSupplierProductLink> | null>(null)
</script>

<template>
    <div v-if="data">
    <div class="overflow-x-auto [scrollbar-width:thin]">
        <table class="w-full min-w-[56rem] table-fixed text-sm">
            <thead class="border-b border-gray-200 text-left text-xs text-gray-500">
                <tr>
                    <th class="w-40 px-4 py-2 font-medium">{{ ctrans("SKU") }}</th>
                    <th class="w-56 px-4 py-2 font-medium">{{ ctrans("Product") }}</th>
                    <th class="w-36 px-4 py-2 font-medium">{{ ctrans("Specs (agent)") }}</th>
                    <th class="w-36 px-4 py-2 font-medium">{{ ctrans("Compliance") }}</th>
                    <th class="w-36 px-4 py-2 font-medium">{{ ctrans("Label") }}</th>
                    <th class="px-4 py-2 font-medium">{{ ctrans("Still missing") }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-for="item in data.rows" :key="item.id" class="align-top">
                    <td class="px-4 py-3">
                        <Link :href="route(item.route.name, item.route.parameters)" :title="item.code" class="primaryLink block truncate">{{ item.code }}</Link>
                    </td>
                    <td class="px-4 py-3">
                        <div class="truncate text-gray-800" :title="item.name">{{ item.name }}</div>
                        <div v-if="item.supplier_product" class="truncate text-xs text-gray-500">{{ item.supplier_product }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex w-28 items-center gap-2">
                            <ProductDevelopmentProgressBar :done="item.specs.done" :total="item.specs.total" />
                            <span class="text-xs tabular-nums text-gray-500">{{ item.specs.done }}/{{ item.specs.total }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex w-28 items-center gap-2">
                            <ProductDevelopmentProgressBar :done="item.compliance.done" :total="item.compliance.total" />
                            <span class="text-xs tabular-nums text-gray-500">{{ item.compliance.done }}/{{ item.compliance.total }}</span>
                        </div>
                    </td>
                    <td class="truncate px-4 py-3 text-gray-500" :title="item.label_status">{{ item.label_status }}</td>
                    <td class="px-4 py-3">
                        <span v-if="item.still_missing.length" class="inline-block break-words rounded-md bg-amber-50 px-2 py-1 text-xs text-amber-800">
                            {{ item.still_missing.join(", ") }}
                        </span>
                        <span v-else class="inline-block rounded-md bg-green-50 px-2 py-1 text-xs text-green-700">{{ ctrans("Complete") }}</span>
                    </td>
                </tr>
                <tr v-if="!data.rows.length">
                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">{{ ctrans("No SKUs in this development yet") }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="data.editor.skus.length" class="space-y-3 border-t border-gray-100 p-4">
        <h3 class="text-sm font-medium text-gray-700">{{ ctrans("Specs per SKU") }}</h3>
        <ProductDevelopmentSkuChecklist
            v-for="sku in data.editor.skus"
            :key="sku.id"
            :sku="sku"
            :rows="sku.rows.filter((row) => row.role.value !== 'compliance')"
            :updateRoute="data.editor.routes.update"
            :countries="data.editor.countries"
            :defaultOpen="data.editor.skus.length === 1"
            @linkSupplierProduct="(target) => linkDialog?.open(target)"
        />
        <ProductDevelopmentSupplierProductLink ref="linkDialog" :linkRoute="data.editor.routes.supplier_product" :searchRoute="data.editor.routes.supplier_products_search" :currency="data.editor.currency" />
    </div>
    </div>
</template>
