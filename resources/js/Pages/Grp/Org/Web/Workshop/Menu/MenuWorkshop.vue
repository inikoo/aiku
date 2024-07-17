<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { navigation } from '@/Components/Websites/Menu/Descriptor.js'
import draggable from "vuedraggable";
import Button from '@/Components/Elements/Buttons/Button.vue';
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Publish from '@/Components/Publish.vue'
import { notify } from "@kyvg/vue3-notification"

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars } from '@fas';
import { faHeart } from '@far';
import { v4 as uuidv4 } from "uuid"
import { Switch } from '@headlessui/vue'
import EditMode from '@/Components/Websites/Menu/EditMode.vue';
import PreviewMode from '@/Components/Websites/Menu/PreviewMode.vue';

library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars);


const props = defineProps<{
  pageHead: TSPageHeading
  title: string
  uploadImageRoute: routeType
  data: {}
  autosaveRoute: routeType
}>()

const Navigation = ref(navigation)
const selectedNav = ref(0)
const previewMode = ref(false)
const isLoading = ref(false)
const comment = ref("")

const addNavigation = () => {
  Navigation.value.push({
    label: "New Navigation",
    id: uuidv4(),
    type: 'single'
  })
}

const deleteNavigation = (index) => {
  selectedNav.value = null
  Navigation.value.splice(index, 1)
}


const onPublish = async (action) => {
  try {
    // Ensure action is defined and has necessary properties
    if (!action || !action.method || !action.name || !action.parameters) {
      throw new Error('Invalid action parameters')
    }

    isLoading.value = true

    // Make sure route and axios are defined and used correctly
    const response = await axios[action.method](route(action.name, action.parameters), {
      comment: comment.value,
      layout: Navigation.value
    })

    console.log(response)
  } catch (error) {
    // Ensure the error is logged properly
    console.error('Error:', error)

    // Ensure the error notification is user-friendly
    const errorMessage = error.response?.data?.message || error.message || 'Unknown error occurred'
    notify({
      title: 'Something went wrong.',
      text: errorMessage,
      type: 'error',
    })
  } finally {
    // Ensure loading state is updated
    isLoading.value = false
  }
};

</script>

<template>

  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead">
    <template #button-publish="{ action }">
      <!--  <Action v-if="action" :action="action" :dataToSubmit="data" /> -->
      <Publish :isLoading="isLoading" :is_dirty="true" v-model="comment" @onPublish="onPublish(action.route)" />
    </template>
  </PageHeading>
  <div class="grid grid-flow-row-dense grid-cols-4">
    <div class="col-span-1 h-screen bg-slate-200 px-3 py-2 relative">
      <div class="flex justify-between">
        <div class="font-bold text-sm">Navigations:</div>
        <Button type="create" label="Add Navigation" size="xs" v-if="Navigation.length < 8"
          @click="addNavigation"></Button>
      </div>
      <draggable :list="Navigation" ghost-class="ghost" group="column" itemKey="id" class="mt-2 space-y-1"
        :animation="200">
        <template #item="{ element, index }">
          <div @click="selectedNav = index"
            :class="[selectedNav == index ? 'ring-indigo-500' : 'ring-gray-200', 'flex-auto rounded-md p-3 ring-1 ring-inset  bg-white cursor-grab']">
            <div class="flex justify-between gap-x-4">
              <div :class="['py-0.5 text-xs leading-5', selectedNav != index ? 'text-gray-500' : 'text-indigo-500']">
                <span class="font-medium">{{ element.label }}</span>
              </div>
              <div class="flex-none py-0 text-xs leading-5 text-gray-500 cursor-pointer">
                <font-awesome-icon :icon="['fal', 'times']" @click="() => deleteNavigation(index)" />
              </div>
            </div>
          </div>
        </template>
      </draggable>

      <!-- New bottom div with red background and absolute positioning -->
      <div class="absolute inset-x-0 bottom-0 bg-gray-300 p-4 text-white text-center">
        <div class="flex items-center gap-x-2">
          <Switch @click="previewMode = !previewMode"
            class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
            <span aria-hidden="true" :class="previewMode ? 'translate-x-6 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
              class="pointer-events-none inline-block h-full w-1/2 transform rounded-full  shadow-lg ring-0 transition duration-200 ease-in-out" />
          </Switch>
          <div class="text-xs leading-none font-medium cursor-pointer select-none"
            :class="previewMode ? 'text-indigo-500' : ' text-gray-400'">
            Preview Mode
          </div>
        </div>
      </div>
    </div>

    <div class="col-span-3">
      <EditMode v-if="!previewMode" :Navigation="Navigation" :selectedNav="selectedNav"></EditMode>
      <PreviewMode v-if="previewMode" :navigations="Navigation"></PreviewMode>
    </div>
  </div>

</template>


<style scss></style>
