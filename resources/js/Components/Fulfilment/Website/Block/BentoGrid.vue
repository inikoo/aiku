<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import Image from "@/Components/Image.vue"
import { getStyles } from "@/Composables/styles";


library.add(faCube, faLink)

const props = defineProps<{
  modelValue: any
  webpageData: any
  web_block: Object
  id: Number,
  type: String,
  isEditable?: boolean
  properties: {}
}>()


const emits = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'autoSave'): void
}>()



</script>

<template>
  <div :style="getStyles(modelValue.container.properties)">
    <div class="mx-auto max-w-2xl px-6 lg:max-w-7xl lg:px-8">
      <Editor v-model="modelValue.title"  :editable="isEditable"  @update:modelValue="() => emits('autoSave')"/>
      <div class="mt-10 grid gap-4 sm:mt-16 lg:grid-cols-3 lg:grid-rows-2">
        <div class="relative lg:row-span-2">
          <div class="absolute inset-px rounded-lg bg-white lg:rounded-l-[2rem]" />
          <div
            class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] lg:rounded-l-[calc(2rem+1px)]">
            <div class="px-8 pb-3 pt-8 sm:px-10 sm:pb-0 sm:pt-10">
              <Editor v-model="modelValue.column1.text"  :editable="isEditable"  @update:modelValue="() => emits('autoSave')"/>
            </div>
            <div class="relative min-h-[30rem] w-full grow [container-type:inline-size] max-lg:mx-auto max-lg:max-w-sm">
              <div
                class="absolute inset-x-10 bottom-0 top-10 overflow-hidden rounded-t-[12cqw] border-x-[3cqw] border-t-[3cqw] border-gray-700 bg-gray-900 shadow-2xl">
                <img class="size-full object-cover object-top"
                  src="https://tailwindui.com/plus/img/component-images/bento-03-mobile-friendly.png" alt="" />
              </div>
            </div>
          </div>
          <div
            class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5 lg:rounded-l-[2rem]" />
        </div>
        <div class="relative max-lg:row-start-1">
          <div class="absolute inset-px rounded-lg bg-white max-lg:rounded-t-[2rem]" />
          <div
            class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] max-lg:rounded-t-[calc(2rem+1px)]">
            <div class="px-8 pt-8 sm:px-10 sm:pt-10">
              <Editor v-model="modelValue.column2.text"  :editable="isEditable"  @update:modelValue="() => emits('autoSave')"/>
            </div>
            <div class="flex flex-1 items-center justify-center px-8 max-lg:pb-12 max-lg:pt-10 sm:px-10 lg:pb-2">
              <img class="w-full max-lg:max-w-xs"
                src="https://tailwindui.com/plus/img/component-images/bento-03-performance.png" alt="" />
            </div>
          </div>
          <div
            class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5 max-lg:rounded-t-[2rem]" />
        </div>
        <div class="relative max-lg:row-start-3 lg:col-start-2 lg:row-start-2">
          <div class="absolute inset-px rounded-lg bg-white" />
          <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)]">
            <div class="px-8 pt-8 sm:px-10 sm:pt-10">
              <Editor v-model="modelValue.column3.text"  :editable="isEditable"  @update:modelValue="() => emits('autoSave')"/>
            </div>
            <div class="flex flex-1 items-center [container-type:inline-size] max-lg:py-6 lg:pb-2">
              <img class="h-[min(152px,40cqw)] object-cover object-center"
                src="https://tailwindui.com/plus/img/component-images/bento-03-security.png" alt="" />
            </div>
          </div>
          <div class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5" />
        </div>
        <div class="relative lg:row-span-2">
          <div class="absolute inset-px rounded-lg bg-white lg:rounded-r-[2rem]" />
          <div
            class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] lg:rounded-r-[calc(2rem+1px)]">
            <div class="px-8 pb-3 pt-8 sm:px-10 sm:pb-0 sm:pt-10">
              <Editor v-model="modelValue.column1.text"  :editable="isEditable"  @update:modelValue="() => emits('autoSave')"/>
            </div>
            <div class="relative min-h-[30rem] w-full grow [container-type:inline-size] max-lg:mx-auto max-lg:max-w-sm">
              <div
                class="absolute inset-x-10 bottom-0 top-10 overflow-hidden rounded-t-[12cqw] border-x-[3cqw] border-t-[3cqw] border-gray-700 bg-gray-900 shadow-2xl">
                <img class="size-full object-cover object-top"
                  src="https://tailwindui.com/plus/img/component-images/bento-03-mobile-friendly.png" alt="" />
              </div>
            </div>
          </div>
          <div
            class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5 lg:rounded-r-[2rem]" />
        </div>
      </div>
    </div>
  </div>
</template>