<script setup lang="ts">
import { faPresentation } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import dataList from "../data/blogActivity.js"
import  { cloneDeep } from 'lodash'

library.add(faPresentation);
const props = defineProps<{
    onPickBlock: Funcition,
}>();

const list = cloneDeep(dataList.block)

</script>

<template>
    <div class="bg-white">
        <main class="pb-24">
            <div class="px-4 pb-16 text-center sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900">Blocks</h1>
                <p class="mx-auto mt-4 max-w-xl text-base text-gray-500">
                    The secret to a tidy desk? Don't get rid of anything, just put it in really
                    really nice looking containers.
                </p>
            </div>

            <section aria-labelledby="products-heading" class="mx-auto max-w-7xl overflow-hidden sm:px-6 lg:px-8">
                <h2 id="products-heading" class="sr-only">Products</h2>

                <div class="-mx-px grid grid-cols-2 border-l border-gray-200 sm:mx-0 md:grid-cols-3 lg:grid-cols-4">
                    <div v-for="product in list" :key="product.id" @click="()=>onPickBlock(product)"
                        class="group relative border-b border-t border-r p-2">
                        <div class="w-32 h-32 overflow-hidden rounded-lg group-hover:opacity-75 mx-auto">
                            <font-awesome-icon :icon="product.icon" class="h-full w-full object-cover object-center" />
                        </div>
                        <div class="mt-2 text-center">
                            <h3 class="text-sm font-medium text-gray-900">
                                <a :href="product.href">
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    {{ product.name }}
                                </a>
                            </h3>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
