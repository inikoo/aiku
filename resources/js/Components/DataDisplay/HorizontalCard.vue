<script setup lang="ts">
import { ref, watch } from "vue";
import Chart from "primevue/chart";

// Define props for dynamic data and options
const props = defineProps<{
  labels: string[]; // Labels for the metrics (e.g., ["LCP", "FID", "CLS"])
  data: number[]; // Data values (e.g., [85, 90, 75])
  backgroundColors: string[]; // Colors for the bars (e.g., ["#4caf50", "#4caf50", "#f44336"])
}>();

// Generate chart data dynamically based on props
const chartData = ref({
  labels: props.labels,
  datasets: [
    {
      label: "Core Web Vitals",
      data: props.data,
      backgroundColor: props.backgroundColors,
      barPercentage: 0.8,
      categoryPercentage: 0.8,
    },
  ],
});

// Define chart options
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false, // Hide legend
    },
    tooltip: {
      callbacks: {
        label: (context: any) => `${context.raw}%`, // Add '%' in tooltips
      },
    },
  },
  scales: {
    x: {
      grid: {
        display: false, // Hide grid lines on x-axis
      },
      ticks: {
        color: "#333",
      },
    },
    y: {
      beginAtZero: true,
      max: 100, // Ensure 100% scale for y-axis
      grid: {
        color: "#ddd",
      },
      ticks: {
        color: "#333",
      },
    },
  },
});
</script>

<template>
  <div class="p-4 bg-white border rounded shadow-sm">
    <h3 class="text-sm font-medium mb-4">Core Web Vitals</h3>
    <Chart type="bar" :data="chartData" :options="chartOptions" class="h-60" />
  </div>
</template>
