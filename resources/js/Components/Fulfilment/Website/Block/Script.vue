<script setup lang="ts">
import { getStyles } from "@/Composables/styles";
import { faCube, faStar } from "@fortawesome/free-solid-svg-icons";
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/swiper-bundle.css"; // Swiper styles
import { onMounted } from 'vue';

library.add(faCube, faStar);

// Props
const props = defineProps<{
  modelValue: any,
  properties: {}
}>();

// Function to load the Reviews.io widget after the component is mounted
onMounted(() => {
  const script = document.createElement("script");
  script.type = "text/javascript";
  script.innerHTML = props.modelValue;
  /*   script.innerHTML = `
      richSnippetReviewsWidgets('carousel-inline-widget-810', {
        store: 'ancient-wisdom-marketing-ltd',
        widgetName: 'carousel-inline',
        primaryClr: '#f47e27',
        neutralClr: '#f4f4f4',
        reviewTextClr: '#2f2f2f',
        ratingTextClr: '#2f2f2f',
        layout: 'fullWidth',
        numReviews: 21
      });
    `; */
  /*  document.getElementById('reviews-widget')?.appendChild(script); */

});
</script>

<template>
  <div class="w-full py-12 px-8 flex gap-x-10">
    <!-- Left side: REVIEWS.io Widget -->
    <div class="w-1/2">
      <h2 class="text-2xl font-bold mb-4">Customer Reviews</h2>
      <div id="reviews-widget">this review place</div>
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

.review-card {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
</style>
