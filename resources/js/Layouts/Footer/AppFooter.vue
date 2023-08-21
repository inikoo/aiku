<script setup lang="ts">
import { ref, Ref } from 'vue'
import FooterTabActiveUsers from '@/Layouts/Footer/FooterTabActiveUsers.vue'
import FooterTabLanguage from '@/Layouts/Footer/FooterTabLanguage.vue'

const props = defineProps<{
  appScope: string
}>()

const isTabActive: Ref<boolean | string> = ref(false)

</script>

<template>
  <footer class="z-20 fixed w-screen bottom-0 right-0  text-white bg-black">
    <!-- Helper: Outer background (close popup purpose) -->
    <div class="fixed z-40 right-0 top-0 bg-transparent w-screen h-screen"
         @click="isTabActive = !isTabActive"
         :class="[isTabActive ? '' : 'hidden']" />

    <div class="flex justify-between">
      <!-- Left: Logo Section -->
      <div class="pl-4 flex items-center gap-x-1.5 py-1">
        <img src="@/../art/logo/png/2.png" alt="Wowsbar" class="h-4 select-none">
      </div>

      <!-- Right: Tab Section -->
      <div class="flex items-end flex-row-reverse text-sm">
        <FooterTabActiveUsers :isTabActive="isTabActive" @isTabActive="(value: any) => isTabActive = value" :appScope="appScope"/>
        <FooterTabLanguage :isTabActive="isTabActive" @isTabActive="(value: any) => isTabActive = value" />
      </div>
    </div>
  </footer>
</template>



