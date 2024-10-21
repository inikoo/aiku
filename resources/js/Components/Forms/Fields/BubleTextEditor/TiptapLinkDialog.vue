<script setup lang="ts">
import { ref, onMounted } from "vue"
import Dialog from "./Dialog.vue"
import PureInput from "@/Components/Pure/PureInput.vue"

const props = defineProps<{ show: boolean; currentUrl?: string }>()
const emit = defineEmits(["close", "update"])
const inputLinkRef = ref<string>()

function closeDialog() {
  emit("close")
}

function update() {
  emit("update", inputLinkRef.value)
  emit("close")
}

onMounted(() => {
  inputLinkRef.value = props.currentUrl ?? ""
})
</script>


<template>
    <Dialog title="Link" :show="show" @close="closeDialog">
      <form @submit.prevent="update">
        <div class="flex flex-col space-y-5">
            <div class="select-none text-sm text-gray-600 mb-2">Link</div>
            <PureInput type="url" id="input-link-url" v-model="inputLinkRef" />
  
          <div class="flex flex-row justify-end space-x-3">
            <button
              type="button"
              class="rounded-md px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100"
              @click="closeDialog"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded-md bg-blue-700 px-4 py-3 text-sm font-medium text-white hover:bg-opacity-80"
            >
              Save
            </button>
          </div>
        </div>
      </form>
    </Dialog>
  </template>
  
 