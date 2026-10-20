<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-15m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref } from "vue"
import { Head } from "@inertiajs/vue3"
import { useFitToViewport } from "@/Components/Goods/ProductDevelopment/useFitToViewport"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import ProductDevelopmentViewSwitch from "@/Components/Goods/ProductDevelopment/ProductDevelopmentViewSwitch.vue"
import ProductDevelopmentFilterBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentFilterBar.vue"
import ProductDevelopmentBoardCard, { type ProductDevelopmentBoardCardData } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentBoardCard.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faList, faColumns } from "@fal"
library.add(faLightbulbOn, faList, faColumns)

type Card = ProductDevelopmentBoardCardData

interface Column {
    position: number
    label: string
    cards: Card[]
}

defineProps<{
    title: string
    pageHead: PageHeadingTypes
    viewSwitch: any[]
    columns: Column[]
    filterBar: any
    roles: Record<string, { label: string, icon: string, color: string }>
    truncated: boolean
}>()

const boardElement = ref<HTMLElement | null>(null)
const { height: boardHeight, fit: fitBoard } = useFitToViewport(boardElement, 24, 280, "(min-width: 0px)")

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #otherBefore>
            <ProductDevelopmentViewSwitch :options="viewSwitch" />
        </template>
    </PageHeading>

    <ProductDevelopmentFilterBar :filterBar="filterBar" routeName="grp.product_developments.board" @toggled="fitBoard" />

    <p v-if="truncated" class="px-4 pt-2 text-xs text-amber-700">
        {{ ctrans("Showing the first 500 developments, use the filters to narrow down") }}
    </p>

    <div ref="boardElement" class="flex gap-3 overflow-x-auto px-4 pb-2 [scrollbar-width:thin] md:px-6" :style="{ height: boardHeight }">
        <section v-for="column in columns" :key="column.position" class="flex min-h-0 w-72 shrink-0 flex-col rounded-md border border-gray-200 bg-gray-50">
            <header class="flex shrink-0 items-center justify-between border-b border-gray-200 px-3 py-2 text-sm font-medium text-gray-700">
                <span>{{ column.position <= 7 ? column.position + ". " : "" }}{{ column.label }}</span>
                <span class="tabular-nums text-gray-400">{{ column.cards.length }}</span>
            </header>

            <div class="flex min-h-0 flex-1 flex-col gap-2 overflow-y-auto p-2 [scrollbar-width:thin]">
                <ProductDevelopmentBoardCard
                    v-for="card in column.cards"
                    :key="card.slug"
                    :card="card"
                    :roles="roles"
                />

                <p v-if="!column.cards.length" class="px-1 py-4 text-center text-xs text-gray-400">
                    {{ ctrans("Nothing here") }}
                </p>
            </div>
        </section>
    </div>
</template>
