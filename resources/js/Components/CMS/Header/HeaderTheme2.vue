<script setup lang="ts">
import Button from '@/Components/Elements/Buttons/Button.vue'
import Image from "@/Components/Image.vue"

const props = defineProps<{
  data: Object
  layout : Object
}>()

const emits = defineEmits();

const changeLogo = async (element) => {
    emits('changeLogo',element)
};

</script>

<template>
  <header :style="`background-color: ${layout?.color};`">
    <nav class="mx-auto flex items-center justify-between p-6 lg:px-8" aria-label="Global">
      <a href="#" class="-m-1.5 p-1.5">
        <span class="sr-only">Your Company</span>
        <label for="faviconUpload" class="flex justify-center items-center cursor-pointer">
          <input type="file" id="faviconUpload" accept="image/*" style="display: none" @change="changeLogo" />
          <Image :src="data.logoSrc" class="h-8 w-auto"/>
        </label>
      </a>
      <div class="flex gap-3">
        <div class="hidden lg:flex lg:flex-1 lg:justify-end">
          <a href="#" class="flex text-sm font-semibold leading-6">
            <Button :class="`bg-${layout.colorScheme}-500`">
              Appointment
            </Button>
          </a>
        </div>
        <div class="text-gray-300">|</div>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end">
          <a class="text-sm font-semibold leading-6 text-gray-900"><Button :class="`bg-${layout.colorScheme}-500`">
             log in
            </Button></a>
        </div>
      </div>
    </nav>
  </header>
</template>
  
 