<script setup lang="ts">
import { computed, ref, watch } from "vue"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faAlignLeft, faAlignCenter, faAlignRight } from "@far"
import { faCheck, faTimes } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { ctrans } from "@/Composables/useTrans"
import {
  IMAGE_WIDTH_PRESETS,
  MAXIMUM_IMAGE_WIDTH_PERCENT,
  MINIMUM_IMAGE_WIDTH_PERCENT,
  ImageAlignment,
  imageWidthPercent,
  toImageWidth,
} from "@/Components/Forms/Fields/BubleTextEditor/CustomResizeImage/imageLayout"

const props = defineProps<{
  show: boolean
  image: {
    src?: string | null
    alt?: string | null
    width?: string | null
    float?: ImageAlignment | null
  }
}>()

const emit = defineEmits<{
  (e: "close"): void
  (e: "update", value: { alt: string, width: string, float: ImageAlignment }): void
}>()

const visible = ref(props.show)
const altText = ref(props.image.alt ?? "")
const widthPercent = ref<number | null>(imageWidthPercent(props.image.width) ?? MAXIMUM_IMAGE_WIDTH_PERCENT)
const alignment = ref<ImageAlignment>(props.image.float ?? "none")

watch(
  () => props.show,
  (isShown) => (visible.value = isShown)
)

const widthPresetOptions = IMAGE_WIDTH_PRESETS.map((percent) => ({ label: `${percent}%`, value: String(percent) }))

const widthPreset = computed<string | undefined>({
  get: () => (widthPercent.value === null ? undefined : String(widthPercent.value)),
  set: (preset) => (widthPercent.value = Number(preset)),
})

const alignmentOptions: Array<{ label: string, value: ImageAlignment, icon: any }> = [
  { label: ctrans("Left"), value: "left", icon: faAlignLeft },
  { label: ctrans("Centre"), value: "none", icon: faAlignCenter },
  { label: ctrans("Right"), value: "right", icon: faAlignRight },
]

function closeDialog() {
  visible.value = false
  emit("close")
}

function update() {
  emit("update", {
    alt: altText.value.trim(),
    width: toImageWidth(widthPercent.value ?? MAXIMUM_IMAGE_WIDTH_PERCENT),
    float: alignment.value,
  })
  closeDialog()
}
</script>

<template>
  <Dialog
    v-model:visible="visible"
    :header="ctrans('Image settings')"
    modal
    class="w-full max-w-md"
    @hide="closeDialog"
  >
    <form @submit.prevent="update" class="flex flex-col space-y-5">
      <div v-if="image.src" class="flex justify-center rounded-md bg-gray-50 p-2">
        <img :src="image.src" :alt="altText" class="max-h-32 w-auto rounded object-contain" />
      </div>

      <div>
        <label for="input-image-alt-text" class="mb-2 block select-none text-sm text-gray-600">
          {{ ctrans("Alternative text") }}
        </label>
        <InputText
          id="input-image-alt-text"
          v-model="altText"
          class="w-full"
          :placeholder="ctrans('Describe what the picture shows')"
        />
        <p class="mt-1 text-xs text-gray-500">
          {{ ctrans("Read out by screen readers and used by search engines. Leave it empty when the picture is only decoration.") }}
        </p>
      </div>

      <div>
        <label for="input-image-width" class="mb-2 block select-none text-sm text-gray-600">
          {{ ctrans("Width") }}
        </label>
        <div class="flex flex-wrap items-center gap-3">
          <SegmentedToggle v-model="widthPreset" :options="widthPresetOptions" :ariaLabel="ctrans('Width')" />
          <InputNumber
            inputId="input-image-width"
            v-model="widthPercent"
            :min="MINIMUM_IMAGE_WIDTH_PERCENT"
            :max="MAXIMUM_IMAGE_WIDTH_PERCENT"
            suffix="%"
            :inputStyle="{ width: '5rem' }"
          />
        </div>
        <p class="mt-1 text-xs text-gray-500">
          {{ ctrans("A share of the width of the article, so the picture scales with the screen.") }}
        </p>
      </div>

      <div>
        <div class="mb-2 select-none text-sm text-gray-600">
          {{ ctrans("Alignment") }}
        </div>
        <SegmentedToggle v-model="alignment" :options="alignmentOptions" :ariaLabel="ctrans('Alignment')" />
        <p v-if="alignment !== 'none'" class="mt-1 text-xs text-gray-500">
          {{ ctrans("The text wraps around the picture.") }}
        </p>
      </div>

      <div class="flex justify-end gap-3">
        <Button type="tertiary" :label="ctrans('Cancel')" @click="closeDialog">
          <template #icon>
            <FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
          </template>
        </Button>
        <Button type="tertiary" nativeType="submit" :label="ctrans('Apply')">
          <template #icon>
            <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
          </template>
        </Button>
      </div>
    </form>
  </Dialog>
</template>
