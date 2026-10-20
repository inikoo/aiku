<!--
  - Author Louis Perez
  - Created on 16-10-2026-02h-50m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faSortAmountDown, faSortAmountUp } from "@fal"
library.add(faSortAmountDown, faSortAmountUp)

const order = defineModel<"newest" | "oldest">({ default: "newest" })
</script>

<template>
    <button
        type="button"
        class="flex items-center gap-1 rounded px-1.5 py-0.5 text-xs text-gray-500 hover:bg-gray-50 hover:text-gray-700"
        @click="order = order === 'newest' ? 'oldest' : 'newest'"
    >
        <FontAwesomeIcon :icon="order === 'newest' ? 'fal fa-sort-amount-down' : 'fal fa-sort-amount-up'" fixed-width aria-hidden="true" />
        {{ order === "newest" ? ctrans("Newest first") : ctrans("Oldest first") }}
    </button>
</template>
