<script setup lang="ts">
import { defineProps, withDefaults, ref, onMounted } from "vue";
import Chart from "primevue/chart";

// Props definition
const props = withDefaults(
  defineProps<{
    showRedBorder: boolean;
    widgetData: {
      value: string;
      description: string;
      status: "success" | "warning" | "danger" | "information" | "neutral";
      type?: "number" | "currency";
      currency_code?: string;
    };
    visual?: {
      type: string;
      label: string[];
      value: number[];
    };
  }>(),
  {
    widgetData: () => ({
      value: "0",
      description: "",
      status: "information",
    }),
  }
);



</script>

<template>
  <div
    class="bg-white shadow-md rounded-lg p-6 flex flex-col h-full w-full border border-gray-200"
  >
    <div class="flex-grow">
    
  </div>
</template>
