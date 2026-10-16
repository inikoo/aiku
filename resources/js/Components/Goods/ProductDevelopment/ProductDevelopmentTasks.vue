<!--
  - Author Louis Perez
  - Created on 16-10-2026-19h-25m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import StaffTaskPanel from "@/Components/Tasks/StaffTaskPanel.vue"

defineProps<{
    data: {
        model_type: "ProductDevelopment"
        model_id: number
    }
}>()
</script>

<template>
    <div v-if="data" class="p-4">
        <StaffTaskPanel :modelType="data.model_type" :modelId="data.model_id" />
    </div>
</template>
