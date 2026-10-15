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

defineProps<{
    title: string
}>()

const isOpen = ref(true)
</script>

<template>
    <section class="rounded-lg border border-gray-200 bg-white">
        <header class="flex items-center gap-2 px-4 py-2.5" :class="isOpen ? 'border-b border-gray-100' : ''">
            <h3 class="flex-1 text-sm font-semibold text-gray-800">{{ title }}</h3>
            <slot name="actions" />
            <button
                type="button"
                class="flex h-6 w-6 items-center justify-center rounded text-gray-400 hover:bg-gray-50 hover:text-gray-600"
                :aria-expanded="isOpen"
                :aria-label="isOpen ? ctrans('Collapse') : ctrans('Expand')"
                @click="isOpen = !isOpen"
            >
                <FontAwesomeIcon icon="fal fa-chevron-down" class="transition-transform" :class="isOpen ? '' : '-rotate-90'" fixed-width aria-hidden="true" />
            </button>
        </header>
        <div v-show="isOpen">
            <slot />
        </div>
    </section>
</template>
