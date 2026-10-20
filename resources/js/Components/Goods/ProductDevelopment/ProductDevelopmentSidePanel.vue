<!--
  - Author Louis Perez
  - Created on 16-10-2026-02h-50m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref } from "vue"
import { ctrans } from "@/Composables/useTrans"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronDown } from "@fal"
library.add(faChevronDown)

const props = withDefaults(defineProps<{
    title: string
    icon?: string | null
    defaultOpen?: boolean
}>(), {
    icon: null,
    defaultOpen: true,
})

const isOpen = ref(props.defaultOpen)
</script>

<template>
    <section class="rounded-lg border border-gray-200 bg-white">
        <header
            class="flex cursor-pointer select-none items-center gap-2 rounded-t-lg px-4 py-2.5 transition-colors hover:bg-gray-50"
            :class="isOpen ? 'border-b border-gray-100' : 'rounded-b-lg'"
            @click="isOpen = !isOpen"
        >
            <h3 class="flex min-w-0 flex-1 items-center gap-2 text-sm font-semibold text-gray-800">
                <FontAwesomeIcon v-if="icon" :icon="icon" class="shrink-0 text-gray-400" fixed-width aria-hidden="true" />
                <span class="truncate">{{ title }}</span>
                <slot name="meta" />
            </h3>
            <div v-if="$slots.actions" class="cursor-default" @click.stop>
                <slot name="actions" />
            </div>
            <button
                type="button"
                class="flex h-6 w-6 items-center justify-center rounded text-gray-400 hover:text-gray-600"
                :aria-expanded="isOpen"
                :aria-label="isOpen ? ctrans('Collapse :title', { title }) : ctrans('Expand :title', { title })"
                @click.stop="isOpen = !isOpen"
            >
                <FontAwesomeIcon icon="fal fa-chevron-down" class="transition-transform" :class="isOpen ? '' : '-rotate-90'" fixed-width aria-hidden="true" />
            </button>
        </header>
        <div v-if="!isOpen && $slots.summary" class="cursor-pointer px-4 pb-3 -mt-1" @click="isOpen = true">
            <slot name="summary" />
        </div>
        <div v-show="isOpen">
            <slot />
        </div>
    </section>
</template>
