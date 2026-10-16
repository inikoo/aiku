<!--
  - Author Louis Perez
  - Created on 16-10-2026-23h-35m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import ProductDevelopmentChecklistRow from "@/Components/Goods/ProductDevelopment/ProductDevelopmentChecklistRow.vue"
import ProductDevelopmentProgressBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentProgressBar.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import type { ChecklistRow, ChecklistSku } from "@/Components/Goods/ProductDevelopment/productDevelopmentChecklist"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronDown, faLink } from "@fal"
library.add(faChevronDown, faLink)

const props = defineProps<{
    sku: ChecklistSku
    rows: ChecklistRow[]
    updateRoute: string
    countries: { value: number, label: string }[]
    skuRoute?: routeType
    defaultOpen?: boolean
}>()

const emit = defineEmits<{
    linkSupplierProduct: [sku: ChecklistSku]
}>()

const open = ref(props.defaultOpen ?? false)

const required = computed(() => props.rows.filter((row) => row.required))
const done = computed(() => required.value.filter((row) => row.state !== "missing").length)
const missing = computed(() => required.value.length - done.value)
const needsSupplierProduct = computed(() => props.rows.some((row) => row.needs_supplier_product))
</script>

<template>
    <section class="rounded-lg border border-gray-200">
        <header class="flex flex-wrap items-center gap-x-3 gap-y-1 px-3 py-2">
            <button type="button" class="flex min-w-0 flex-1 items-center gap-2 text-left" :aria-expanded="open" @click="open = !open">
                <FontAwesomeIcon icon="fal fa-chevron-down" class="shrink-0 text-gray-400 transition-transform" :class="open ? '' : '-rotate-90'" fixed-width aria-hidden="true" />
                <span class="min-w-0">
                    <span class="block truncate text-sm font-medium text-gray-800">{{ sku.code }} <span class="font-normal text-gray-500">{{ sku.name }}</span></span>
                    <span class="block text-xs text-gray-500">
                        <template v-if="sku.supplier_product">{{ ctrans("Supplier product") }}: {{ sku.supplier_product.code }}</template>
                        <template v-else>{{ ctrans("No supplier product linked") }}</template>
                    </span>
                </span>
            </button>

            <div class="flex items-center gap-2">
                <div class="w-24"><ProductDevelopmentProgressBar :done="done" :total="required.length" /></div>
                <span class="text-xs tabular-nums text-gray-500">{{ done }}/{{ required.length }}</span>
                <span v-if="missing" class="rounded-md bg-amber-50 px-2 py-0.5 text-xs text-amber-800">{{ ctrans(":count missing", { count: String(missing) }) }}</span>
                <span v-else class="rounded-md bg-green-50 px-2 py-0.5 text-xs text-green-700">{{ ctrans("Complete") }}</span>
            </div>
        </header>

        <div v-show="open" class="border-t border-gray-100 px-3">
            <div v-if="(needsSupplierProduct || !sku.supplier_product) && sku.can_link_supplier_product" class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 py-2 text-xs text-gray-600">
                <span>{{ ctrans("Carton, pack and lead time are saved on the supplier product") }}</span>
                <Button type="tertiary" size="xs" icon="fal fa-link" :label="ctrans('Link supplier product')" @click="emit('linkSupplierProduct', sku)" />
            </div>
            <div v-if="skuRoute" class="border-b border-gray-100 py-2 text-xs">
                <Link :href="route(skuRoute.name, skuRoute.parameters)" class="primaryLink">{{ ctrans("Open trade unit") }}</Link>
            </div>
            <ul class="divide-y divide-gray-100">
                <ProductDevelopmentChecklistRow v-for="row in rows" :key="row.id" :row="row" :updateRoute="updateRoute" :countries="countries" />
                <li v-if="!rows.length" class="py-3 text-sm text-gray-400">{{ ctrans("No checklist items") }}</li>
            </ul>
        </div>
    </section>
</template>
