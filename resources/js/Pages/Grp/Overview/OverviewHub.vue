<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faExclamationCircle } from "@fortawesome/free-solid-svg-icons";
import { capitalize } from "@/Composables/capitalize";
import { inject, ref, computed, onMounted, onUnmounted } from "vue";
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure";
import {
  faBoothCurtain,
  faBoxes,
  faBoxOpen,
  faBrowser,
  faCoin,
  faDolly,
  faEnvelope,
  faEnvelopeOpenText,
  faExchangeAlt,
  faFilter,
  faForklift,
  faInboxOut,
  faIndustryAlt,
  faInventory,
  faLocationArrow,
  faMailBulk,
  faRoad,
  faTrashAlt,
  faTruckLoading,
  faUser,
  faUserAlien,
  faUserCircle,
  faUserHeadset,
  faUsers,
  faWarehouseAlt,
  faPaperPlane,
  faScrollOld,
  faPhoneVolume,
  faRaygun
} from "@fal";
import SectionTable from "@/Components/Table/SectionTable.vue";
import { faAngleDown, faAngleUp } from "@far";

const props = defineProps<{
  title: string;
  pageHead: any;
  data: {
    data: Array<{
      section: string;
      data: Array<{
        name: string;
        icon: string;
        route: string;
        count: number;
      }>;
    }>;
  };
}>();

const locale = inject("locale", aikuLocaleStructure);

library.add(faExclamationCircle, faInboxOut, faUser, faFilter, faBoxes, faAngleDown, faAngleUp, faBrowser, faTrashAlt, faCoin, faBoothCurtain, faRoad, faUserAlien, faEnvelopeOpenText, faEnvelope, faUserCircle, faExchangeAlt, faDolly, faBoxOpen, faTruckLoading, faForklift, faUsers, faUserHeadset, faWarehouseAlt, faInventory, faLocationArrow, faIndustryAlt, faMailBulk, faPaperPlane,faPhoneVolume, faRaygun, faScrollOld);

// Search functionality
const searchQuery = ref("");
const isFilterVisible = ref(false);

const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement;
  if (!target.closest(".search-container")) {
    isFilterVisible.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});

</script>

<template>
  <!-- Page Header -->
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead" />

  <!-- Dashboard Grid -->
  <div class="grid grid-cols-12 m-3 gap-4">
    <!-- Left Column -->
    <div class="col-span-6 space-y-4">
      <!-- Predicted Months DataTable -->
      <SectionTable :data="props.data.data" />
    </div>

    <!-- Middle Column -->
    <div class="col-span-3 space-y-4">
      <div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
        <h3 class="text-gray-500 font-semibold text-lg mb-2">The Nutrition Store</h3>
        <div class="relative bg-gray-100 border border-green-500 rounded-lg px-2 py-3 mb-3">
          <div>
            <p class="text-4xl font-bold leading-tight">275</p>
            <p class="text-gray-500 text-base mt-1">Total orders today</p>
          </div>

          <div
            class="absolute bottom-0 right-0 transform translate-x-1/2 translate-y-1/2 bg-green-500 text-white rounded-full w-8 h-8 flex items-center justify-center shadow">
            ✓
          </div>
        </div>
        <div>
          <p class="text-4xl font-bold leading-tight ">$6,058</p>
          <p class="text-gray-500 text-base mt-1">Sales today</p>
        </div>
      </div>

      <div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
        <h3 class="text-gray-500 font-semibold text-lg mb-2">The Yoga Store</h3>
        <div class="relative py-3 mb-1" style="height: 102px">
          <!-- Adjusted height -->
          <div>
            <p class="text-4xl font-bold leading-tight">46</p>
            <p class="text-gray-500 text-base mt-1">Ad spend this week</p>
          </div>
          <!-- Red Icon in Bottom Right -->
        </div>
        <div>
          <p class="text-4xl font-bold leading-tight ">$2,596</p>
          <p class="text-gray-500 text-base mt-1">Sales today</p>
        </div>
      </div>
    </div>

    <!-- Added Beside Right -->
    <div class="col-span-3 space-y-4">
      <div class="flex flex-col gap-4">
        <!-- Card 1: Cart Abandonment Rate -->
        <div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
          <p class="text-4xl font-bold leading-tight text-gray-700">
            45<span class="text-3xl">%</span>
          </p>
          <p class="text-gray-500 text-base mt-2">Cart abandonment rate</p>
        </div>

        <!-- Card 2: Ad Spend This Week -->
        <div
          class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-red-400 relative">
          <p class="text-4xl font-bold leading-tight text-gray-700">$2,345</p>
          <p class="text-gray-500 text-base mt-2">Ad spend this week</p>
          <!-- Red Exclamation Icon -->
          <div
            class="absolute bottom-0 right-0 transform translate-x-1/2 translate-y-1/2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md">
            !
          </div>
        </div>

        <!-- Card 3: Total Newsletter Subscribers -->
        <div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
          <p class="text-4xl font-bold leading-tight text-gray-700">
            55.7<span class="text-xl">K</span>
          </p>
          <p class="text-gray-500 text-base mt-2">Total newsletter subscribers</p>
          <!-- Progress Bar -->
          <div class="mt-4">
            <div class="w-full bg-gray-300 rounded-full h-1.5">
              <div class="bg-blue-500 h-1.5 rounded-full" style="width: 55%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
              <span>55%</span>
              <span>100%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.search-container {
  transition: width 0.3s ease;
}
</style>
