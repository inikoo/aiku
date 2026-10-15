<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"

defineProps<{
    tab?: string
}>()
</script>

<template>
    <div class="p-8 text-center text-sm text-gray-400">
        {{ ctrans("Nothing here yet") }}
    </div>
</template>
