<script setup lang="ts">
import { onMounted, ref } from "vue"
/* import { useDropzone } from "vue3-dropzone" */
import Dialog from "./Dialog.vue"
import axios from "axios"
/* import type ImageData from "@/models/image" */

defineProps<{
  show: boolean
}>()
const emit = defineEmits<{
  (e: "close"): void
  (e: "insert", url: string): void
}>()

/* const { getRootProps, getInputProps, isDragActive } = useDropzone({
  accept: "image/png,image/jpeg",
  multiple: false,
  onDrop: onDropImage,
  noClick: true,
}) */

const imageListRef = ref<ImageData[]>([])

function closeDialog() {
  emit("close")
}

function onDropImage(acceptedFiles: any[]) {
  if (acceptedFiles.length === 0) {
    return
  }

  const formData = new FormData()
  formData.append("file", acceptedFiles[0])

  axios
    .post("http://localhost:8080/files", formData, {
      headers: {
        "Content-type": "multipart/form-data",
      },
    })
    .then(() => {
      loadData()
    })
}

function loadData() {
  axios.get("http://localhost:8080/files").then((result) => {
    imageListRef.value = result.data
  })
}

function insertImage(url: string) {
  emit("insert", url)
  closeDialog()
}

onMounted(() => {
  loadData()
})
</script>


<template>
    <Dialog title="Pilih Gambar" :show="show" @close="closeDialog">
      gfddgsfdg
    </Dialog>
  </template>
  
