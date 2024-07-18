<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { navigation } from '@/Components/Websites/Menu/Descriptor'
import draggable from "vuedraggable";
import Button from '@/Components/Elements/Buttons/Button.vue';
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Publish from '@/Components/Publish.vue'
import { notify } from "@kyvg/vue3-notification"
import ListMenu from '@/Components/Websites/Menu/ListMenu'
import { v4 as uuidv4 } from "uuid"
import { Switch } from '@headlessui/vue'
import EditMode from '@/Components/Websites/Menu/EditMode.vue';
import PreviewMode from '@/Components/Websites/Menu/PreviewMode.vue';
import Modal from '@/Components/Utils/Modal.vue'
import axios from 'axios'
import { useColorTheme } from '@/Composables/useStockList'
import { Head } from '@inertiajs/vue3'

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars } from '@fas';
import { faHeart } from '@far';


library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars);


const props = defineProps<{
  pageHead: TSPageHeading
  title: string
  uploadImageRoute: routeType
  data: {}
  autosaveRoute: routeType
}>()


const Navigation = ref(props.data.menu.data ? props.data.menu.data : navigation)
const selectedNav = ref(0)
const previewMode = ref(false)
const isLoading = ref(false)
const comment = ref("")
const isModalOpen = ref(false)
const usedTemplates = ref(props.data.menu.key ? props.data.menu.key :  'menu1')
const colorThemed = props.data?.color ? props.data?.color : {color : [...useColorTheme[0]]}

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
      layout: { data : Navigation.value, key : usedTemplates.value }
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


const onPickTemplate = (menu) => {
    isModalOpen.value = false
    usedTemplates.value = menu.key
}

</script>

<template>
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead">
    <template #button-publish="{ action }">
      <!--  <Action v-if="action" :action="action" :dataToSubmit="data" /> -->
      <Publish :isLoading="isLoading" :is_dirty="true" v-model="comment" @onPublish="onPublish(action.route)" />
    </template>
  </PageHeading>
  <div class="h-screen grid grid-flow-row-dense grid-cols-4">
    <div class="col-span-1 bg-slate-200 px-3 py-2 flex flex-col h-full">
      <div class="flex justify-between">
        <div class="font-bold text-sm">Navigations:</div>
        <Button type="create" label="Add Navigation" size="xs" v-if="Navigation.length < 8 && !previewMode" @click="addNavigation"></Button>
        <Button icon="fas fa-th-large" label="Template" size="xs" v-if="previewMode" @click="isModalOpen = true"></Button>
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

    </div>

    <div class="col-span-3  h-full overflow-auto">
      <EditMode v-if="!previewMode" :Navigation="Navigation" :selectedNav="selectedNav"></EditMode>
      <PreviewMode v-if="previewMode" :navigations="Navigation" :template="usedTemplates" :headerData="props.data.header" :colorThemed="colorThemed"></PreviewMode>
    </div>

    <div class="bg-gray-300 p-4 text-white text-center fixed bottom-5 w-full">
        <div class="flex items-center gap-x-2">
            <Switch @click="previewMode = !previewMode"
                class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                <span aria-hidden="true"
                    :class="previewMode ? 'translate-x-6 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
                    class="pointer-events-none inline-block h-full w-1/2 transform rounded-full  shadow-lg ring-0 transition duration-200 ease-in-out"></span>
            </Switch>
            <div class="text-xs leading-none font-medium cursor-pointer select-none"
                :class="previewMode ? 'text-indigo-500' : ' text-gray-400'">
                Preview Mode
            </div>
        </div>
    </div>
  </div>

  <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-2/5">
        <div tag="div"
            class="relative grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-4 overflow-y-auto overflow-x-hidden">
            <div v-for="menu in ListMenu.listTemplate" :key="menu.key"  @click="() => onPickTemplate(menu)"
                class="group flex items-center gap-x-2 relative border border-gray-300 px-3 py-2 rounded cursor-pointer hover:bg-gray-100">
                <div class="flex items-center justify-center">
                    <FontAwesomeIcon :icon='menu.icon' class='' fixed-width aria-hidden='true' />
                </div>
                <h3 class="text-sm font-medium">
                    {{ menu.name }}
                </h3>
            </div>
        </div>
    </Modal>


</template>


<style scss></style>
