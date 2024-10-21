<script setup lang="ts">
import { ref } from "vue"
import Dialog from "./Dialog.vue"
import PureInput from "@/Components/Pure/PureInput.vue"

defineProps<{
  show: boolean
}>()

const emit = defineEmits(["close", "insert"])

const inputYoutubeUrlRef = ref<string>("")

function closeDialog() {
  emit("close")
}

function onSubmit() {
  emit("insert", inputYoutubeUrlRef.value)
  inputYoutubeUrlRef.value = ""
  closeDialog()
}
</script>

<template>
    <Dialog title="Tambah Video Youtube" :show="show" @close="closeDialog">
      <form @submit.prevent="onSubmit">
        <div class="flex flex-col space-y-5">
          
            <div class="select-none text-sm text-gray-600 mb-2">Youtube Link</div>
            <PureInput type="url"
              id="input-add-youtube-url"
              v-model="inputYoutubeUrlRef"
              required />
 
          <div class="flex flex-row justify-end space-x-3">
            <button
              type="button"
              class="rounded-md px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100"
              @click="closeDialog"
            >
              Batal
            </button>
            <button
              type="submit"
              class="rounded-md bg-blue-700 px-4 py-3 text-sm font-medium text-white hover:bg-opacity-80"
            >
              Tambah
            </button>
          </div>
        </div>
      </form>
    </Dialog>
  </template>
  
