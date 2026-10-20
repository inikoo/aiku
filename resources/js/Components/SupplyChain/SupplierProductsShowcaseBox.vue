<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

defineProps<{
    supplierProducts: {
        id: number
        code: string
        name: string | null
        supplier_name: string | null
        is_discontinued: boolean
        route: routeType
        supplier_route: routeType | null
    }[]
    discreet?: boolean
}>()
</script>

<template>
    <div v-if="discreet" class="text-xs text-gray-400">
        <template v-if="supplierProducts.length">
            {{ ctrans("Suppliers") }}:
            <span v-for="(supplierProduct, index) in supplierProducts" :key="supplierProduct.id" :class="{ 'line-through': supplierProduct.is_discontinued }">
                <template v-if="index">, </template>
                <Link v-if="supplierProduct.supplier_route" :href="route(supplierProduct.supplier_route.name, supplierProduct.supplier_route.parameters)" class="hover:underline">{{ supplierProduct.supplier_name }}</Link>
                <template v-else>{{ supplierProduct.supplier_name }}</template>
                (<Link :href="route(supplierProduct.route.name, supplierProduct.route.parameters)" class="whitespace-nowrap hover:underline">{{ supplierProduct.code }}</Link>)
            </span>
        </template>
    </div>
    <div v-else class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3 text-xs font-medium uppercase tracking-wide text-gray-400">
            {{ ctrans("Suppliers") }}
        </div>
        <div v-if="supplierProducts.length" class="divide-y divide-gray-100">
            <div v-for="supplierProduct in supplierProducts" :key="supplierProduct.id"
                class="flex items-center gap-3 px-4 py-2.5 text-sm"
                :class="{ 'opacity-50': supplierProduct.is_discontinued }">
                <div class="min-w-0 flex-1">
                    <Link v-if="supplierProduct.supplier_route"
                        :href="route(supplierProduct.supplier_route.name, supplierProduct.supplier_route.parameters)"
                        class="font-medium text-gray-700 hover:underline">
                        {{ supplierProduct.supplier_name }}
                    </Link>
                    <span v-else class="font-medium text-gray-700">{{ supplierProduct.supplier_name ?? ctrans("Unknown supplier") }}</span>
                    <div class="truncate text-xs text-gray-400">{{ supplierProduct.name }}</div>
                </div>
                <span v-if="supplierProduct.is_discontinued" class="text-xs text-red-500">{{ ctrans("Discontinued") }}</span>
                <Link :href="route(supplierProduct.route.name, supplierProduct.route.parameters)"
                    class="whitespace-nowrap font-mono text-xs text-gray-600 hover:underline">
                    {{ supplierProduct.code }}
                </Link>
            </div>
        </div>
        <div v-else class="px-4 py-4 text-center text-sm text-gray-400">
            {{ ctrans("No supplier products") }}
        </div>
    </div>
</template>
