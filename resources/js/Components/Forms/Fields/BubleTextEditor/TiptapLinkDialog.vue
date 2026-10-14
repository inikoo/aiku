<script setup lang="ts">
import { ref, watch } from "vue"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import Checkbox from "primevue/checkbox"
import { ctrans } from "@/Composables/useTrans"
import Button from "@/Components/Elements/Buttons/Button.vue";

const props = defineProps<{ show: boolean; currentUrl?: string; underline?: boolean; showUnderlineOption?: boolean }>()
const emit = defineEmits(["close", "update"])

const inputLinkRef = ref<string>(props.currentUrl)
const isUnderlined = ref<boolean>(props.underline ?? true)
const visible = ref(props.show)

watch(
  () => props.show,
  (val) => (visible.value = val)
)

watch(
  () => props.currentUrl,
  (url) => (inputLinkRef.value = url ?? "")
)

watch(
  () => props.underline,
  (underline) => (isUnderlined.value = underline ?? true)
)

function closeDialog() {
  visible.value = false
  emit("close")
}

function update() {
  emit("update", inputLinkRef.value, props.showUnderlineOption ? isUnderlined.value : undefined)
  closeDialog()
}
</script>

<template>
 
  <Dialog
    v-model:visible="visible"
    header="Link"
    modal
    class="w-full max-w-md"
    @hide="closeDialog"
    :contentStyle="{overflowY : 'visible'}"
  >
    <form @submit.prevent="update" class="flex flex-col space-y-5">
      <div>
        <label for="input-link-url" class="block text-sm text-gray-600 mb-2 select-none">
          Link
        </label>
        <InputText
          id="input-link-url"
          v-model="inputLinkRef"
          type="url"
          class="w-full"
          placeholder="https://example.com"
        />
      </div>

      <div v-if="showUnderlineOption" class="flex items-center gap-2">
        <Checkbox v-model="isUnderlined" inputId="input-link-underline" binary />
        <label for="input-link-underline" class="text-sm text-gray-600 select-none">
          {{ ctrans("Underline link") }}
        </label>
      </div>

      <div class="flex justify-end gap-3">
        <Button
          label="Cancel"
          type="tertiary"
          @click="closeDialog"
        />
        <Button
          label="Save"
          type="save"
          @click="update"
        />
      </div>
    </form>
  </Dialog>
</template>
