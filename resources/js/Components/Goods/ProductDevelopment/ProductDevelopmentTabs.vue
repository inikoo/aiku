<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-55m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref } from "vue"
import type { Navigation } from "@/types/Tabs"
import { useScrollArrows } from "@/Composables/useScrollArrows"
import ScrollFadeArrow from "@/Components/Utils/ScrollFadeArrow.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

defineProps<{
    navigation: Navigation
    current: string
}>()

const emits = defineEmits<{
    (e: "update:tab", value: string): void
}>()

const scroller = ref<HTMLElement | null>(null)
const { canScrollLeft, canScrollRight, scrollBy } = useScrollArrows(scroller)
</script>

<template>
    <div class="relative isolate">
    <nav ref="scroller" class="flex gap-1 overflow-x-auto overflow-y-hidden border-b border-gray-200 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" :aria-label="'Tabs'">
        <button
            v-for="(tab, tabKey) in navigation"
            :key="tabKey"
            type="button"
            class="-mb-px flex items-center gap-1.5 whitespace-nowrap border-b-2 px-3 py-2.5 text-sm transition-colors"
            :class="String(tabKey) === current
                ? 'border-[--app-accent] font-medium text-[--app-accent]'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
            :aria-current="String(tabKey) === current ? 'page' : undefined"
            @click="emits('update:tab', String(tabKey))"
        >
            <FontAwesomeIcon v-if="tab.icon" :icon="tab.icon" fixed-width aria-hidden="true" />
            {{ tab.title }}
            <span v-if="typeof tab.number === 'number'" class="ml-0.5 tabular-nums" :class="String(tabKey) === current ? 'text-[--app-accent]' : 'text-gray-400'">{{ tab.number }}</span>
        </button>
    </nav>
    <ScrollFadeArrow direction="left" :visible="canScrollLeft" @click="scrollBy(-1)" />
    <ScrollFadeArrow direction="right" :visible="canScrollRight" @click="scrollBy(1)" />
    </div>
</template>
