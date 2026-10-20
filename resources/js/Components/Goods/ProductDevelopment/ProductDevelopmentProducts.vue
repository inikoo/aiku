<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import ProductDevelopmentProgressBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentProgressBar.vue"
import ProductDevelopmentRoleSpecs from "@/Components/Goods/ProductDevelopment/ProductDevelopmentRoleSpecs.vue"
import ProductDevelopmentSupplierProductLink from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSupplierProductLink.vue"
import type { ChecklistEditorData, ChecklistRow } from "@/Components/Goods/ProductDevelopment/productDevelopmentChecklist"

interface Tally {
    done: number
    total: number
    missing: number
}

const props = defineProps<{
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

const MISSING_PREVIEW = 4
const expanded = ref<Record<number, boolean>>({})
const missingShown = (item: { id: number, still_missing: string[] }) => expanded.value[item.id] ? item.still_missing : item.still_missing.slice(0, MISSING_PREVIEW)

const linkDialog = ref<InstanceType<typeof ProductDevelopmentSupplierProductLink> | null>(null)

const roles = computed(() => {
    const found = new Map<string, ChecklistRow["role"]>()
    for (const sku of props.data?.editor?.skus ?? []) {
        for (const row of sku.rows) {
            if (row.role.value !== "compliance" && !found.has(row.role.value)) {
                found.set(row.role.value, row.role)
            }
        }
    }

    return [...found.values()]
})
</script>

<template>
    <div v-if="data" class="space-y-4">
    <ul class="space-y-2 md:hidden">
        <li v-for="item in data.rows" :key="item.id" class="rounded-lg border border-gray-200 bg-white px-4 py-3">
            <div class="flex items-baseline gap-2">
                <Link :href="route(item.route.name, item.route.parameters)" class="primaryLink shrink-0 font-medium">{{ item.code }}</Link>
                <span class="min-w-0 truncate text-sm text-gray-700">{{ item.name }}</span>
            </div>
            <div v-if="item.supplier_product" class="truncate text-xs text-gray-500">{{ item.supplier_product }}</div>

            <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1">
                <div>
                    <div class="mb-1 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ ctrans("Specs (agent)") }}</span>
                        <span class="tabular-nums">{{ item.specs.done }}/{{ item.specs.total }}</span>
                    </div>
                    <ProductDevelopmentProgressBar :done="item.specs.done" :total="item.specs.total" />
                </div>
                <div>
                    <div class="mb-1 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ ctrans("Compliance") }}</span>
                        <span class="tabular-nums">{{ item.compliance.done }}/{{ item.compliance.total }}</span>
                    </div>
                    <ProductDevelopmentProgressBar :done="item.compliance.done" :total="item.compliance.total" />
                </div>
            </div>

            <div class="mt-3 text-xs text-gray-500">
                {{ ctrans("Label") }}: <span class="text-gray-700">{{ item.label_status }}</span>
            </div>

            <div class="mt-3 border-t border-gray-100 pt-2.5">
                <template v-if="item.still_missing.length">
                    <div class="mb-1.5 text-xs text-gray-500">{{ ctrans("Still missing (:count)", { count: String(item.still_missing.length) }) }}</div>
                    <div class="flex flex-wrap gap-1.5">
                        <span v-for="missing in missingShown(item)" :key="missing" class="rounded-md bg-amber-50 px-2 py-0.5 text-xs text-amber-800">{{ missing }}</span>
                        <button
                            v-if="item.still_missing.length > MISSING_PREVIEW"
                            type="button"
                            class="rounded-md px-2 py-0.5 text-xs font-medium text-[--app-accent] hover:bg-[--app-accent-soft]"
                            @click="expanded[item.id] = !expanded[item.id]"
                        >
                            {{ expanded[item.id] ? ctrans("Show less") : ctrans("+:count more", { count: String(item.still_missing.length - MISSING_PREVIEW) }) }}
                        </button>
                    </div>
                </template>
                <span v-else class="inline-block rounded-md bg-green-50 px-2 py-0.5 text-xs text-green-700">{{ ctrans("Complete") }}</span>
            </div>
        </li>
        <li v-if="!data.rows.length" class="rounded-lg border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-400">{{ ctrans("No SKUs in this development yet") }}</li>
    </ul>

    <div class="hidden overflow-x-auto rounded-lg border border-gray-200 bg-white [scrollbar-width:thin] md:block">
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

    <div v-if="roles.length" class="space-y-4">
        <ProductDevelopmentRoleSpecs
            v-for="role in roles"
            :key="role.value"
            :role="role"
            :skus="data.editor.skus"
            :updateRoute="data.editor.routes.update"
            :countries="data.editor.countries"
            :options="data.editor.options"
            @linkSupplierProduct="(target) => linkDialog?.open(target)"
        />
        <ProductDevelopmentSupplierProductLink ref="linkDialog" :linkRoute="data.editor.routes.supplier_product" :searchRoute="data.editor.routes.supplier_products_search" :currency="data.editor.currency" />
    </div>
    </div>
</template>
