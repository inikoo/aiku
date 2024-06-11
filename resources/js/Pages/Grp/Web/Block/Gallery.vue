<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Button from '@/Components/Elements/Buttons/Button.vue';
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"

library.add(faCube, faLink)

const props = defineProps<{
  modelValue: any
}>()


const emits = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
  (e: 'autoSave'): void
}>()

</script>
<template>

  <div class="bg-white">
    <div class="py-2 sm:py-2 lg:mx-auto lg:max-w-7xl lg:px-8">
        <div class="w-full overflow-x-auto pb-6">
          <ul role="list"
            class="mx-4 inline-flex space-x-8 sm:mx-6 lg:mx-0 lg:grid lg:grid-cols-4 lg:gap-x-8 lg:space-x-0">
            <li v-for="product in modelValue.value" :key="product.id"
              class="inline-flex w-64 flex-col lg:w-auto">
              <div>
                <div class="aspect-h-1 aspect-w-1 w-full rounded-md bg-gray-200">
                  <img :src="product.imageSrc" :alt="product.imageAlt"
                    class="w-full object-cover object-center group-hover:opacity-75" />
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
</template>
