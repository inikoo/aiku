<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import ProductDevelopmentMissingBadge from "@/Components/Goods/ProductDevelopment/ProductDevelopmentMissingBadge.vue"
import { routeType } from "@/types/route"

defineProps<{
    data: {
        id: number
        code: string
        name: string
        status: string
        route: routeType
        supplier_product: string | null
        number_missing: number
    }[]
}>()
</script>

<template>
    <div v-if="data" class="p-4">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="py-2 pr-4 font-medium">{{ ctrans("SKU") }}</th>
                    <th class="py-2 pr-4 font-medium">{{ ctrans("Name") }}</th>
                    <th class="py-2 pr-4 font-medium">{{ ctrans("Status") }}</th>
                    <th class="py-2 pr-4 font-medium">{{ ctrans("Supplier product") }}</th>
                    <th class="py-2 text-right font-medium">{{ ctrans("Missing") }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-for="item in data" :key="item.id">
                    <td class="py-2 pr-4">
                        <Link :href="route(item.route.name, item.route.parameters)" class="primaryLink">{{ item.code }}</Link>
                    </td>
                    <td class="py-2 pr-4 text-gray-800">{{ item.name }}</td>
                    <td class="py-2 pr-4 text-gray-600">{{ item.status }}</td>
                    <td class="py-2 pr-4 text-gray-600">{{ item.supplier_product ?? "—" }}</td>
                    <td class="py-2 text-right"><ProductDevelopmentMissingBadge :count="item.number_missing" /></td>
                </tr>
                <tr v-if="!data.length">
                    <td colspan="5" class="py-4 text-center text-gray-400">{{ ctrans("No SKUs in this development yet") }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
