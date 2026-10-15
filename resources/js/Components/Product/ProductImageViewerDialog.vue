<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
  faChevronCircleLeft,
  faChevronCircleRight,
  faDownload,
  faTimes,
} from '@fal'
import { ctrans } from '@/Composables/useTrans'
import Image from '@common/Components/Image.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Dialog from 'primevue/dialog'

const props = defineProps<{
  images: { source: any; thumbnail?: any; zoom?: any; alt?: string; original_url?: string | null; download_url?: string | null }[]
  video?: string
  showVideo?: boolean
}>()

const visible = defineModel<boolean>('visible', { required: true })
const selectedIndex = defineModel<number>('index', { default: 0 })

const currentImage = computed(() => props.images[selectedIndex.value])

const onPrevNavigation = () => {
  selectedIndex.value = (selectedIndex.value - 1 + props.images.length) % props.images.length
}

const onNextNavigation = () => {
  selectedIndex.value = (selectedIndex.value + 1) % props.images.length
}

const isDownloading = ref(false)

const getFilenameFromDisposition = (disposition: string | null): string | null => {
  const encodedMatch = disposition?.match(/filename\*=(?:UTF-8|utf-8)''([^;]+)/)
  if (encodedMatch) return decodeURIComponent(encodedMatch[1])

  return disposition?.match(/filename="?([^";]+)"?/)?.[1] ?? null
}

const downloadCurrentImage = async () => {
  const downloadUrl = currentImage.value?.download_url
  if (!downloadUrl || isDownloading.value) return

  isDownloading.value = true

  try {
    const response = await fetch(downloadUrl, { credentials: 'same-origin' })
    if (!response.ok) throw new Error(response.statusText)

    const objectUrl = URL.createObjectURL(await response.blob())
    const link = document.createElement('a')
    link.href = objectUrl
    link.download = getFilenameFromDisposition(response.headers.get('Content-Disposition')) || currentImage.value?.alt || 'image'
    link.click()
    setTimeout(() => URL.revokeObjectURL(objectUrl), 1000)
  } catch {
    window.location.href = downloadUrl
  } finally {
    isDownloading.value = false
  }
}

function onKeydown(e: KeyboardEvent) {
  if (!visible.value) return
  if (e.key === 'Escape' || e.key === 'Esc') visible.value = false
  if (props.showVideo) return
  if (e.key === 'ArrowLeft') onPrevNavigation()
  if (e.key === 'ArrowRight') onNextNavigation()
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Dialog v-model:visible="visible" modal dismissable-mask close-on-escape :closable="false" :show-header="false"
    :pt="{ content: { class: '!p-0 !bg-transparent' } }"
    class="w-[calc(100vw-3rem)] max-w-6xl !bg-transparent !shadow-none !border-0 !border-transparent">    
    <div class="relative w-full flex flex-col items-center justify-center" @click.self="visible = false">
      <div class="w-full flex justify-end mb-2" @click.self="visible = false">
        <button class="text-white text-3xl hover:text-gray-300 z-50" aria-label="Close viewer"
          @click="visible = false">
          <FontAwesomeIcon :icon="faTimes" fixed-width />
        </button>
      </div>

      <!-- Image Viewer -->
      <div v-if="!showVideo && currentImage?.original_url"
        class="relative w-full h-[75vh] flex items-center justify-center overflow-hidden" @click.self="visible = false">
        <img :src="currentImage.original_url" :alt="currentImage?.alt || `Image ${selectedIndex + 1}`"
          class="max-h-full max-w-full object-contain" />
      </div>
      <div v-else-if="!showVideo" class="relative w-full h-[75vh] overflow-hidden rounded select-none"
        @click.self="visible = false">
        <Image :src="currentImage?.zoom || currentImage?.source"
          :alt="currentImage?.alt || `Image ${selectedIndex + 1}`" :style="{ objectFit: 'contain' }" :imageCover="true" />
      </div>

      <div v-if="!showVideo && currentImage?.download_url" class="w-full flex justify-center mt-4"
        @click.self="visible = false">
        <Button type="white-w-outline" :icon="faDownload" :label="ctrans('Download image')" :loading="isDownloading"
          @click="downloadCurrentImage" />
      </div>

      <!-- Video Viewer -->
      <div v-else class="w-full aspect-video flex items-center justify-center">
        <iframe class="w-full h-full rounded-lg" :src="video" frameborder="0" allow="autoplay; fullscreen"
          allowfullscreen></iframe>
      </div>

      <!-- Navigation (for image only) -->
      <template v-if="!showVideo && images.length > 1">
        <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-4xl z-40" aria-label="Previous image"
          @click="onPrevNavigation">
          <FontAwesomeIcon :icon="faChevronCircleLeft" fixed-width />
        </button>
        <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-4xl z-40" aria-label="Next image"
          @click="onNextNavigation">
          <FontAwesomeIcon :icon="faChevronCircleRight" fixed-width />
        </button>
      </template>
    </div>
  </Dialog>
</template>

<style scoped lang="scss">
button {
  outline: none;
}

:deep(.p-dialog-mask) {
  background-color: rgba(0, 0, 0, 0.9) !important;
}

:deep(.p-dialog) {
  background: transparent !important;
  box-shadow: none !important;
  border: none !important;
}
</style>
