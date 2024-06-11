<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

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
        <div class="mx-auto max-w-7xl py-2 sm:px-6 sm:py-2 lg:px-8">
            <div
                class="relative isolate overflow-hidden bg-gray-900 px-6 py-24 text-center shadow-2xl sm:rounded-3xl sm:px-16">
                <Editor v-model="modelValue.headline" @update:modelValue="() => emits('autoSave')" />
                <!--   <h2 class="mx-auto max-w-2xl text-3xl font-bold tracking-tight text-white sm:text-4xl">Boost your productivity today.</h2> -->
                <Editor v-model="modelValue.description" @update:modelValue="() => emits('autoSave')" />

                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <div
                        style="display: inline-block; border-radius: 0.375rem; background-color: #ffffff; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; color: #1f2937; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); text-align: center; cursor: pointer;">
                        <Editor v-model="modelValue.button" @update:modelValue="() => emits('autoSave')" />
                    </div>
                </div>
                <svg viewBox="0 0 1024 1024"
                    class="absolute left-1/2 top-1/2 -z-10 h-[64rem] w-[64rem] -translate-x-1/2 [mask-image:radial-gradient(closest-side,white,transparent)]"
                    aria-hidden="true">
                    <circle cx="512" cy="512" r="512" fill="url(#827591b1-ce8c-4110-b064-7cb85a0b1217)"
                        fill-opacity="0.7" />
                    <defs>
                        <radialGradient id="827591b1-ce8c-4110-b064-7cb85a0b1217">
                            <stop stop-color="#7775D6" />
                            <stop offset="1" stop-color="#E935C1" />
                        </radialGradient>
                    </defs>
                </svg>
            </div>
        </div>
    </div>
</template>