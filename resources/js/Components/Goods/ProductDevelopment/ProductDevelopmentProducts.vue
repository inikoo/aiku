<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import ProductDevelopmentProgressBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentProgressBar.vue"

interface Tally {
    done: number
    total: number
    missing: number
}

defineProps<{
    data: {
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
}>()
</script>

<template>
    <div v-if="data" class="overflow-x-auto [scrollbar-width:thin]">
        <table class="min-w-full text-sm">
            <thead class="border-b border-gray-200 text-left text-xs text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">{{ ctrans("SKU") }}</th>
                    <th class="px-4 py-2 font-medium">{{ ctrans("Product") }}</th>
                    <th class="px-4 py-2 font-medium">{{ ctrans("Specs (agent)") }}</th>
                    <th class="px-4 py-2 font-medium">{{ ctrans("Compliance") }}</th>
                    <th class="px-4 py-2 font-medium">{{ ctrans("Label") }}</th>
                    <th class="px-4 py-2 font-medium">{{ ctrans("Still missing") }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-for="item in data" :key="item.id" class="align-top">
                    <td class="px-4 py-3">
                        <Link :href="route(item.route.name, item.route.parameters)" class="primaryLink whitespace-nowrap">{{ item.code }}</Link>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-gray-800">{{ item.name }}</div>
                        <div v-if="item.supplier_product" class="text-xs text-gray-500">{{ item.supplier_product }}</div>
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
                    <td class="px-4 py-3 text-gray-500">{{ item.label_status }}</td>
                    <td class="px-4 py-3">
                        <span v-if="item.still_missing.length" class="inline-block rounded-md bg-amber-50 px-2 py-1 text-xs text-amber-800">
                            {{ item.still_missing.join(", ") }}
                        </span>
                        <span v-else class="inline-block rounded-md bg-green-50 px-2 py-1 text-xs text-green-700">{{ ctrans("Complete") }}</span>
                    </td>
                </tr>
                <tr v-if="!data.length">
                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">{{ ctrans("No SKUs in this development yet") }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
