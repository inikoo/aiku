<!--
  - Author Louis Perez
  - Created on 17-10-2026-15h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed } from "vue"
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import DesignBoxNet from "@/Components/Goods/ProductDevelopment/Design/DesignBoxNet.vue"
import { hasSize, type DesignImage, type DesignLayout } from "@/Components/Goods/ProductDevelopment/Design/boxNet"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faBoxOpen, faTag, faLock, faArrowRight } from "@fal"
library.add(faLightbulbOn, faBoxOpen, faTag, faLock, faArrowRight)

const props = defineProps<{
    data: {
        rounds: {
            value: string
            label: string
            icon: string
            unlocked: boolean
            latest: { version: number, status: { value: string, label: string, color: string, icon: string }, layout: DesignLayout } | null
            progress: { done: number, total: number } | null
            skus: { id: number, code: string, version: number | null, status: { label: string, color: string } | null }[]
            route: routeType
        }[]
        images: DesignImage[]
    }
}>()

const imageMap = computed<Record<number, string | null>>(() => Object.fromEntries((props.data?.images ?? []).map((image) => [image.id, image.url])))
</script>

<template>
    <div v-if="data" class="grid gap-4 p-4 md:grid-cols-3">
        <component
            :is="round.unlocked ? Link : 'div'"
            v-for="round in data.rounds"
            :key="round.value"
            :href="round.unlocked ? route(round.route.name, round.route.parameters) : undefined"
            class="group flex flex-col rounded-lg border border-gray-200 bg-white transition-colors"
            :class="round.unlocked ? 'hover:border-[--app-accent]' : 'opacity-70'"
        >
            <header class="flex items-center gap-2 border-b border-gray-100 px-3 py-2 text-sm">
                <FontAwesomeIcon :icon="round.unlocked ? round.icon : 'fal fa-lock'" class="text-gray-400" fixed-width aria-hidden="true" />
                <span class="font-medium text-gray-800">{{ round.label }}</span>
                <span v-if="round.progress && round.progress.total" class="ml-auto rounded px-1.5 py-0.5 text-xs" :class="round.progress.done === round.progress.total ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'">{{ ctrans(":done of :total SKUs approved", { done: String(round.progress.done), total: String(round.progress.total) }) }}</span>
                <span v-else-if="round.latest" class="ml-auto rounded px-1.5 py-0.5 text-xs" :class="productDevelopmentColor(round.latest.status.color).count">v{{ round.latest.version }} · {{ round.latest.status.label }}</span>
            </header>
            <div class="flex flex-1 items-center justify-center bg-gray-50 p-3">
                <DesignBoxNet v-if="round.latest && hasSize(round.latest.layout)" :layout="round.latest.layout" :images="imageMap" :maxHeight="260" class="pointer-events-none" />
                <p v-else class="py-12 text-center text-xs text-gray-400">
                    {{ !round.unlocked ? ctrans("Opens once the previous round is approved") : round.latest ? ctrans("Box size not set yet") : ctrans("No design yet") }}
                </p>
            </div>
            <ul v-if="round.skus.length" class="flex flex-wrap gap-1.5 border-t border-gray-100 px-3 py-2">
                <li v-for="sku in round.skus" :key="sku.id" class="rounded px-1.5 py-0.5 text-xs" :class="sku.status ? productDevelopmentColor(sku.status.color).count : 'bg-gray-100 text-gray-500'">
                    {{ sku.code }}<template v-if="sku.status"> · v{{ sku.version }} {{ sku.status.label }}</template>
                </li>
            </ul>
            <footer v-if="round.unlocked" class="flex items-center justify-end gap-1.5 px-3 py-2 text-xs text-[--app-accent] group-hover:text-[--app-accent-strong]">
                {{ round.latest ? ctrans("Open design") : ctrans("Start the design") }}
                <FontAwesomeIcon icon="fal fa-arrow-right" fixed-width aria-hidden="true" />
            </footer>
        </component>
    </div>
</template>
