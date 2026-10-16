<!--
  - Author Louis Perez
  - Created on 17-10-2026-17h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import ProductDevelopmentSpecField from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSpecField.vue"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { ctrans } from "@/Composables/useTrans"
import type { ChecklistGroupOptions, ChecklistRow, ChecklistSku } from "@/Components/Goods/ProductDevelopment/productDevelopmentChecklist"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLink, faChevronDown } from "@fal"
library.add(faLink, faChevronDown)

type Filter = "todo" | "done" | "all"

const props = defineProps<{
    role: ChecklistRow["role"]
    skus: ChecklistSku[]
    updateRoute: string
    countries: { value: number, label: string }[]
    options?: ChecklistGroupOptions
}>()

const emit = defineEmits<{
    linkSupplierProduct: [sku: ChecklistSku]
}>()

const titles: Record<string, string> = {
    agent: "Product specs from agent",
    purchasing: "Commercial terms from purchasing",
    compliance: "Compliance details",
}

const entries = computed(() => props.skus.map((sku) => {
    const rows = sku.rows.filter((row) => row.role.value === props.role.value)
    const missing = rows.filter((row) => row.required && row.state === "missing").length

    return { sku, rows, missing, needsSupplierProduct: rows.some((row) => row.needs_supplier_product) }
}).filter((entry) => entry.rows.length))

const todo = computed(() => entries.value.filter((entry) => entry.missing))
const done = computed(() => entries.value.filter((entry) => !entry.missing))

const filter = ref<Filter>(todo.value.length ? "todo" : "all")
const filters = computed(() => [
    { value: "todo" as Filter, label: `${ctrans("To do")} ${todo.value.length}` },
    { value: "done" as Filter, label: `${ctrans("Done")} ${done.value.length}` },
    { value: "all" as Filter, label: `${ctrans("All")} ${entries.value.length}` },
])
const visible = computed(() => filter.value === "todo" ? todo.value : filter.value === "done" ? done.value : entries.value)

const toggled = ref<Record<number, boolean>>({})
const isOpen = (entry: { sku: ChecklistSku, missing: number }) => toggled.value[entry.sku.id] ?? entry.missing > 0

const color = computed(() => productDevelopmentColor(props.role.color))
</script>

<template>
    <section v-if="entries.length" class="rounded-lg border border-gray-200 bg-white">
        <header class="flex flex-wrap items-center gap-x-3 gap-y-2 border-b border-gray-100 px-4 py-3">
            <FontAwesomeIcon :icon="role.icon" :class="color.icon" fixed-width aria-hidden="true" />
            <h3 class="text-sm font-semibold text-gray-900">{{ ctrans(titles[role.value] ?? role.label) }}</h3>
            <span class="text-xs text-gray-500">{{ ctrans(":done of :total SKUs done", { done: String(done.length), total: String(entries.length) }) }}</span>
            <span class="ml-auto hidden text-xs text-gray-500 xl:inline">{{ ctrans("Saved values go straight to the trade unit and supplier product") }}</span>
            <SegmentedToggle v-model="filter" :options="filters" :ariaLabel="ctrans('Show')" class="ml-auto xl:ml-0" />
        </header>

        <div class="divide-y divide-gray-100">
            <div v-for="entry in visible" :key="entry.sku.id">
                <button type="button" class="flex w-full items-center gap-2 px-4 py-2.5 text-left hover:bg-gray-50" :aria-expanded="isOpen(entry)" @click="toggled[entry.sku.id] = !isOpen(entry)">
                    <FontAwesomeIcon icon="fal fa-chevron-down" class="shrink-0 text-gray-400 transition-transform" :class="isOpen(entry) ? '' : '-rotate-90'" fixed-width aria-hidden="true" />
                    <span class="min-w-0 flex-1 truncate text-sm">
                        <Link :href="route(entry.sku.route.name, entry.sku.route.parameters)" class="primaryLink font-medium" @click.stop>{{ entry.sku.code }}</Link>
                        <span class="ml-1.5 text-gray-500">{{ entry.sku.name }}</span>
                    </span>
                    <span v-if="entry.missing" class="shrink-0 rounded bg-amber-50 px-1.5 py-0.5 text-xs text-amber-800">{{ ctrans(":count missing", { count: String(entry.missing) }) }}</span>
                    <span v-else class="shrink-0 rounded bg-green-50 px-1.5 py-0.5 text-xs text-green-700">{{ ctrans("Complete") }}</span>
                </button>

                <div v-show="isOpen(entry)" class="px-4 pb-4">
                    <div v-if="entry.needsSupplierProduct" class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-md bg-amber-50/70 px-3 py-2 text-xs text-amber-800">
                        <span>{{ ctrans("Carton, pack and lead time are saved on the supplier product: link one to :sku first", { sku: entry.sku.code }) }}</span>
                        <Button v-if="entry.sku.can_link_supplier_product" type="tertiary" size="xs" icon="fal fa-link" :label="ctrans('Link supplier product')" @click="emit('linkSupplierProduct', entry.sku)" />
                    </div>
                    <div class="grid gap-x-4 gap-y-4 sm:grid-cols-2 xl:grid-cols-3">
                        <ProductDevelopmentSpecField
                            v-for="row in entry.rows"
                            :key="row.id"
                            :row="row"
                            :updateRoute="updateRoute"
                            :countries="countries"
                            :options="options"
                            :class="['textarea', 'pictograms', 'label_marks', 'ingredients', 'photos'].includes(row.input_type) ? 'sm:col-span-2 xl:col-span-3' : ''"
                        />
                    </div>
                </div>
            </div>
            <p v-if="!visible.length" class="px-4 py-6 text-center text-sm text-gray-400">
                {{ filter === "todo" ? ctrans("Nothing left to do") : ctrans("No SKU complete yet") }}
            </p>
        </div>
    </section>
</template>
