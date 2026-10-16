<!--
  - Author Louis Perez
  - Created on 16-10-2026-12h-40m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import Select from "primevue/select"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faShoppingBag, faUserTie, faFileInvoiceDollar, faShieldCheck, faPencilRuler, faGlobe, faStamp } from "@fal"
library.add(faShoppingBag, faUserTie, faFileInvoiceDollar, faShieldCheck, faPencilRuler, faGlobe, faStamp)

interface RoleOption {
    value: string
    label: string
    icon?: string
    color?: string
}

const props = defineProps<{
    options: RoleOption[]
    placeholder?: string
    size?: "small" | "large"
}>()

const model = defineModel<string | null>()

const optionFor = (value: unknown) => props.options.find((option) => option.value === value)
</script>

<template>
    <Select v-model="model" :options="options" optionLabel="label" optionValue="value" :size="size" :placeholder="placeholder" class="w-full min-w-0" overlayClass="text-sm" :pt="{ label: { class: 'min-w-0' } }">
        <template #value="{ value, placeholder: emptyText }">
            <span v-if="optionFor(value)" class="flex min-w-0 items-center gap-1.5">
                <FontAwesomeIcon v-if="optionFor(value)!.icon" :icon="optionFor(value)!.icon!" :class="productDevelopmentColor(optionFor(value)!.color).icon" fixed-width aria-hidden="true" />
                <span class="truncate">{{ optionFor(value)!.label }}</span>
            </span>
            <span v-else class="text-gray-400">{{ emptyText }}</span>
        </template>
        <template #option="{ option }">
            <span class="flex items-center gap-1.5">
                <FontAwesomeIcon v-if="option.icon" :icon="option.icon" :class="productDevelopmentColor(option.color).icon" fixed-width aria-hidden="true" />
                {{ option.label }}
            </span>
        </template>
    </Select>
</template>
