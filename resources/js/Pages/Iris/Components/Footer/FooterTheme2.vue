<!--
  This example requires some changes to your config:
  
  ```
  // tailwind.config.js
  module.exports = {
    // ...
    plugins: [
      // ...
      require('@tailwindcss/forms'),
    ],
  }
  ```
-->
<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faFacebook, faInstagram, faTwitter, faGithub, faYoutube } from "@fortawesome/free-brands-svg-icons"
import { faMapMarkerAlt, faEnvelope, faBalanceScale, faBuilding, faPhone, faMap } from "@fortawesome/free-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
import draggable from "vuedraggable";
import { ref } from 'vue'
import TitleItem from '../Fields/Input.vue'
import TextArea from '../Fields/TextArea.vue'
library.add(faFacebook, faInstagram, faTwitter, faGithub, faYoutube, faMapMarkerAlt, faEnvelope, faBalanceScale, faBuilding, faPhone, faMap)

const props = defineProps<{
    social: Object,
    navigation: Object,
    changeColums: Function,
    selectedColums: Function
    columSelected: Object
    saveItemTitle: Function
    saveTextArea:Function
}>()

console.log('props', props)


const childLog = (a, b, c) => {
    window.console.log('sdfsdf', a, b, c);
}



</script>

<template>
    <footer class="bg-gray-50 px-6" aria-labelledby="footer-heading">
        <h2 id="footer-heading" class="sr-only">Footer</h2>
        <div class="mx-auto max-w-7xl px-6 pb-8 pt-20 sm:pt-24 lg:px-8 lg:pt-12">
            <div class="xl:grid xl:gap-x-24 xl:px-6">
                <!-- Navigations -->
                <draggable :list="navigation" group="navigation" @change="(e) => changeColums(e)" itemKey="id"
                    class="flex gap-8 xl:col-span-2 cursor-grab">
                    <template #item="{ element, index }">
                        <div :class="['space-y-3', 'w-1/4', columSelected.id !== element.id ? '' : 'border']"
                            @click="props.selectedColums(element)">
                            <!-- <h3 class="text-sm font-bold leading-6 text-gray-700 capitalize">{{ element.title }}</h3> -->
                            <TitleItem :data="element" :save="props.saveItemTitle" />
                            <div v-if="element.type == 'list'">
                                <draggable :list="element.data" group="navigationData" @change="childLog" itemKey="name">
                                    <template #item="{ element: child, index: childIndex }">
                                        <ul role="list">
                                            <li :key="child.name">
                                                <a :href="child.href"
                                                    class="space-y-3 text-sm leading-6 text-gray-600 hover:text-indigo-500">
                                                    {{ child.name }}</a>
                                            </li>
                                        </ul>
                                    </template>
                                </draggable>
                            </div>

                            <div v-if="element.type == 'description'">
                                <!-- <div class="space-y-3 text-sm leading-6 text-gray-600 hover:text-indigo-500">{{ element.data }}</div> -->
                                <TextArea :data="element" :save="props.saveTextArea"/>
                            </div>

                            <div v-if="element.type == 'info'">
                                <div class="flex flex-col gap-y-5">
                                    <div v-for="address in element.data"
                                        class="grid grid-cols-[auto,1fr] gap-4 items-center justify-start gap-x-3">
                                        <div class="w-5 flex items-center justify-center text-gray-400">
                                            <FontAwesomeIcon :icon="address.icon" :title="address.title"
                                                aria-hidden="true" />
                                        </div>
                                        <span class="leading-5 text-gray-600" v-html="address.value"></span>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </template>
                </draggable>

            </div>

            <!-- Social Media -->
            <div
                class="border-t border-gray-900/10 pt-8 sm:mt-10 flex flex-col md:flex-row items-center justify-between mt-16 lg:mt-18 xl:px-3">
                <div class="flex space-x-6 md:order-2">
                    <a v-for="social in social" :key="social.name" :href="social.href"
                        class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">{{ social.title }}</span>
                        <FontAwesomeIcon :icon="social.icon" class="h-6 w-6" aria-hidden="true" />
                    </a>
                </div>
                <p class="mt-4 text-xs leading-5 text-gray-500 md:order-1 md:mt-0">
                    &copy; 2023 <span class="font-bold">AW Advantage</span>, Inc. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</template>