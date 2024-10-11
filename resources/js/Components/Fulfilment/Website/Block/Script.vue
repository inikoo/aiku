<script setup lang="ts">
import { getStyles } from "@/Composables/styles";
import { faCube, faStar } from "@fortawesome/free-solid-svg-icons";
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

// Import Swiper for the review slider
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/swiper-bundle.css"; // Swiper styles
import { onMounted } from 'vue';

library.add(faCube, faStar);

// Props
const props = defineProps<{
  modelValue: any,
  properties: {}
}>();
// Dummy reviews for the slider (replace this with actual review data)
const reviews = [
  {
    author: "John Doe",
    content: "Amazing product! Highly recommended.",
  },
  {
    author: "Jane Smith",
    content: "Really satisfied with the quality and service.",
  },
  {
    author: "Bob Johnson",
    content: "Good value for money, will purchase again.",
  },
];
</script>

<template>
  <div class="w-full py-12 px-8 flex gap-x-10">
    <!-- Left side: REVIEWS.io Widget -->
    <div class="w-1/2">
      <h2 class="text-2xl font-bold mb-4">Customer Reviews</h2>
      <div id="reviews-widget">this review place</div>
    </div>

    <!-- Right side: Review slider -->
    <div class="w-1/2">
      <h2 class="text-2xl font-bold mb-4">What Our Customers Say</h2>
      this slider people
    </div>
  </div>
</template>

<style>
/* Customize Swiper styles */
.swiper-slide {
  padding: 16px;
  background-color: #f9fafb;
  border-radius: 8px;
}
</style>
