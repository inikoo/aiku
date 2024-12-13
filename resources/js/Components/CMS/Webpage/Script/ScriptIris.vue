<script setup lang="ts">
import { faCube, faStar } from "@fortawesome/free-solid-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import "swiper/swiper-bundle.css" // Swiper styles

library.add(faCube, faStar)

const props = defineProps<{
    fieldValue: {
        value: string
    }
}>()


</script>

<template>
    <div class="w-full py-12 px-8 flex gap-x-10">
        <div v-html="fieldValue.value"></div>
    </div>
</template>
