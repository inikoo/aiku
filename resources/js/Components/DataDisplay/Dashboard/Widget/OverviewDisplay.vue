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

onMounted(() => {
  chartData.value = setChartData();
  chartOptions.value = setChartOptions();
});

const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
  const documentStyle = getComputedStyle(document.documentElement);

  return {
    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
    datasets: [
      {
        label: 'First Dataset',
        data: [65, 59, 80, 81, 56, 55, 40],
        fill: false,
        borderColor: documentStyle.getPropertyValue('--p-cyan-500'),
        tension: 0.4,
      },
      {
        label: 'Second Dataset',
        data: [28, 48, 40, 19, 86, 27, 90],
        fill: false,
        borderColor: documentStyle.getPropertyValue('--p-gray-500'),
        tension: 0.4,
      },
    ],
  };
};

const setChartOptions = () => {
  const documentStyle = getComputedStyle(document.documentElement);
  const textColor = documentStyle.getPropertyValue('--p-text-color');
  const textColorSecondary = documentStyle.getPropertyValue('--p-text-muted-color');
  const surfaceBorder = documentStyle.getPropertyValue('--p-content-border-color');

  return {
    maintainAspectRatio: false, // Disable aspect ratio for full responsiveness
    responsive: true,
    plugins: {
      legend: {
        labels: {
          color: textColor,
        },
      },
    },
    scales: {
      x: {
        ticks: {
          color: textColorSecondary,
        },
        grid: {
          color: surfaceBorder,
        },
      },
      y: {
        ticks: {
          color: textColorSecondary,
        },
        grid: {
          color: surfaceBorder,
        },
      },
    },
  };
};
</script>

<template>
  <div
    class="bg-white shadow-md rounded-lg p-6 flex flex-col h-full w-full border border-gray-200"
  >
    <div class="flex-grow">
      <!-- Chart Component -->
      <Chart type="line" :data="chartData" :options="chartOptions" class="h-full w-full" />
    </div>
  </div>
</template>
