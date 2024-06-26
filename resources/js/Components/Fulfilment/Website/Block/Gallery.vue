<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue";
import Image from "@/Components/Image.vue"
import { ref } from 'vue'
import { cloneDeep } from "lodash";

library.add(faCube, faLink)

const props = defineProps<{
    modelValue: any
    webpageData: any
    web_block : Object
    id: Number,
    type : String
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
    (e: 'autoSave'): void
}>()

const openGallery = ref(false)
const imagePick = ref<number | null>(null);

const setImage = (e) => {
    const data = cloneDeep(props.modelValue)
    data.value[imagePick.value].image = e 
    emits('update:modelValue', data)
    onCloseGallery()
    emits('autoSave')
}

const onUpload = (e) => {
  if (e.data && e.data.length <= 1) {
    const data = cloneDeep(props.modelValue)
    data.value[imagePick.value].image = e.data[0]
    emits('update:modelValue', data)
    onCloseGallery();
    emits('autoSave')
  } else {
    console.error('No files or multiple files detected.');
  }
};

const onOpenGallery = (index : number) =>{
  openGallery.value = true
  imagePick.value = index
}

const onCloseGallery = () =>{
  openGallery.value = false
  imagePick.value = null
}
</script>

<template>
  <div class="bg-white">
    <div class="py-2 sm:py-2 lg:mx-auto lg:max-w-7xl lg:px-8">
        <div class="w-full overflow-x-auto pb-6">
          <ul role="list"
            class="mx-4 inline-flex space-x-8 sm:mx-6 lg:mx-0 lg:grid lg:grid-cols-4 lg:gap-x-8 lg:space-x-0">
            <li v-for="(product,index) in modelValue.value" :key="product.id"
              class="inline-flex w-64 flex-col lg:w-auto">
              <div>
                <div class="aspect-h-1 aspect-w-1 w-full rounded-md bg-gray-200" @click="()=>onOpenGallery(index)" >
                  <img v-if="!product.image" :src="product.imageSrc" :alt="product.imageAlt" class="w-full object-cover object-center group-hover:opacity-75" />
                  <Image v-else :src="modelValue?.image?.source" class="w-full object-cover object-center group-hover:opacity-75"></Image>
                </div>
                <div class="mt-6">
                  <Editor v-model="product.text" @update:modelValue="() => emits('autoSave')" />
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <Gallery 
        :open="openGallery" 
        @on-close="onCloseGallery" 
        :uploadRoutes="route(webpageData?.images_upload_route.name,{ modelHasWebBlocks : id })"  
        @onPick="setImage"
        @onUpload="onUpload"
    >
    </Gallery>


</template>
