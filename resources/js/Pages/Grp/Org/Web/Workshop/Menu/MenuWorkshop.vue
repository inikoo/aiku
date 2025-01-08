<script setup lang="ts">
import { ref, watch, IframeHTMLAttributes } from "vue"
import draggable from "vuedraggable"
import Button from "@/Components/Elements/Buttons/Button.vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import Publish from "@/Components/Publish.vue"
import { notify } from "@kyvg/vue3-notification"
import { v4 as uuidv4 } from "uuid"
import EditMode from "@/Components/CMS/Website/Menus/EditMode/EditMode.vue"
import Modal from "@/Components/Utils/Modal.vue"
import axios from "axios"
import { Head } from "@inertiajs/vue3"
import ScreenView from "@/Components/ScreenView.vue"
import { debounce } from "lodash"
import EmptyState from '@/Components/Utils/EmptyState.vue'
import HeaderListModal from '@/Components/CMS/Fields/ListModal.vue'
import { setIframeView } from "@/Composables/Workshop"
import ProgressSpinner from 'primevue/progressspinner';

import { routeType } from "@/types/route"
import { PageHeading as TSPageHeading } from "@/types/PageHeading"

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import {
  faChevronRight,
  faSignOutAlt,
  faShoppingCart,
  faSearch,
  faChevronDown,
  faTimes,
  faPlusCircle,
  faBars,
} from "@fas"
import { faHeart, faExternalLink } from "@far"


library.add(
  faChevronRight,
  faSignOutAlt,
  faShoppingCart,
  faHeart,
  faSearch,
  faChevronDown,
  faTimes,
  faPlusCircle,
  faBars
)

const props = defineProps<{
  pageHead: TSPageHeading
  title: string
  uploadImageRoute: routeType
  data: {}
  autosaveRoute: routeType
  webBlockTypes: Object
}>()



const Navigation = ref(props.data?.menu)
const selectedNav = ref(0)
const previewMode = ref(false)
const isLoading = ref(false)
const comment = ref("")
const isModalOpen = ref(false)
const isIframeLoading = ref(true)
const iframeClass = ref("w-full h-full")
const _iframe = ref<IframeHTMLAttributes | null>(null)
const iframeSrc = ref(route("grp.websites.header.preview", [route().params["website"]]))

console.log(Navigation.value)
const addNavigation = () => {
  Navigation.value.data.fieldValue.navigation.push({
    label: "New Navigation",
    id: uuidv4(),
    type: "single",
  })
}

const deleteNavigation = (index: Number) => {
  selectedNav.value = null
  Navigation.value?.data?.fieldValue.navigation.splice(index, 1)
}

const onPublish = async (action: routeType, popover: Funcition) => {
  try {
    // Ensure action is defined and has necessary properties
    if (!action || !action.method || !action.name || !action.parameters) {
      throw new Error("Invalid action parameters")
    }

    isLoading.value = true

    // Make sure route and axios are defined and used correctly
    const response = await axios[action.method](route(action.name, action.parameters), {
      comment: comment.value,
      layout: { ...Navigation.value },
    })
    popover.close()
  } catch (error) {
    // Ensure the error is logged properly
    console.error("Error:", error)

    // Ensure the error notification is user-friendly
    const errorMessage =
      error.response?.data?.message || error.message || "Unknown error occurred"
    notify({
      title: "Something went wrong.",
      text: errorMessage,
      type: "error",
    })
  } finally {
    // Ensure loading state is updated
    isLoading.value = false
  }
}

const onSelectBlock = (menu: Object) => {
  isModalOpen.value = false
  Navigation.value = menu
}

const sendToIframe = (data: any) => {
    _iframe.value?.contentWindow.postMessage(data, '*')
}

const autoSave = async (data: object) => {
  try {
    const response = await axios.patch(
      route(props.autosaveRoute.name, props.autosaveRoute.parameters),
      { layout: data }
    )
    sendToIframe({ key: 'reload', value: {} })
  } catch (error: any) {
    notify({
      title: "Something went wrong.",
      text: error.message,
      type: "error",
    })
  }
}

const openFullScreenPreview = () => {
  window.open(iframeSrc.value, "_blank")
}


const handleIframeError = () => {
  console.error('Failed to load iframe content.');
}

const debouncedSendUpdate = debounce((data) => autoSave(data), 1000, {
  leading: false,
  trailing: true,
})

watch(
  Navigation,
  (newVal) => {
    if (newVal) debouncedSendUpdate(newVal)
  },
  { deep: true }
)

</script>

<template>

  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead">
    <template #button-publish="{ action }">
      <Publish :isLoading="isLoading" :is_dirty="true" v-model="comment"
        @onPublish="(popover) => onPublish(action.route, popover)" />
    </template>
  </PageHeading>

  <div v-if="Navigation" class="h-[85vh] grid grid-flow-row-dense grid-cols-4">
    <div class="col-span-1 bg-slate-200 px-3 py-2 flex flex-col h-full">
      <div class="flex justify-between">
        <div class="font-bold text-sm">Navigations:</div>
        <Button type="create" label="Add Navigation" size="xs"
          v-if="Navigation?.data?.fieldValue.navigation?.length < 8 " @click="addNavigation"></Button>
      </div>
      <draggable :list="Navigation.data.fieldValue.navigation" ghost-class="ghost" group="column" itemKey="id"
        class="mt-2 space-y-1" :animation="200">
        <template #item="{ element, index }">
          <div @click="selectedNav = index" :class="[
            selectedNav == index ? 'ring-indigo-500' : 'ring-gray-200',
            'flex-auto rounded-md p-3 ring-1 ring-inset  bg-white cursor-grab',
          ]">
            <div class="flex justify-between gap-x-4">
              <div :class="[
                'py-0.5 text-xs leading-5',
                selectedNav != index ? 'text-gray-500' : 'text-indigo-500',
              ]">
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

    <div class="col-span-3">
      <div class="h-full w-full bg-slate-100">
        <div class="flex justify-between bg-slate-200 border border-b-gray-300">
          <div class="flex">
            <ScreenView @screenView="(e)=>iframeClass = setIframeView(e)" />
            <div class="py-1 px-2 cursor-pointer" title="Desktop view" v-tooltip="'Preview'"
              @click="openFullScreenPreview">
              <FontAwesomeIcon :icon="faExternalLink" aria-hidden="true" />
            </div>
          </div>
          <div class="flex items-center justify-center">
            <div class="text-xs leading-none font-medium cursor-pointer select-none mr-2"
              :class="[previewMode ? 'text-slate-600' : 'text-slate-300']">
              Preview Mode
            </div>
            <Switch @click="previewMode = !previewMode" :class="[previewMode ? 'bg-slate-600' : 'bg-slate-300']"
              class="pr-1 relative inline-flex h-3 w-6 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
              <span aria-hidden="true" :class="previewMode ? 'translate-x-3' : 'translate-x-0'"
                class="pointer-events-none inline-block h-full w-1/2 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
            </Switch>
            <div class="py-1 px-2 cursor-pointer" title="template" @click="isModalOpen = true">
              <FontAwesomeIcon icon="fas fa-th-large" aria-hidden="true" />
            </div>
          </div>
        </div>

        <EditMode v-if="!previewMode" :Navigation="Navigation?.data?.fieldValue?.navigation" :selectedNav="selectedNav" />
        <div v-else class="h-full w-full bg-slate-100">
          <!-- <div v-if="isIframeLoading" class="flex justify-center items-center w-full h-64 p-12 bg-white">
            <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
          </div> -->
          <div v-if="isIframeLoading" class="loading-overlay">
              <ProgressSpinner />
          </div>
          <iframe 
            :src="iframeSrc" 
            :title="props.title" 
            :class="[iframeClass, isIframeLoading ? 'hidden' : '']"
            @error="handleIframeError" 
            @load="isIframeLoading = false" 
            ref="_iframe"
          />
        </div>

      </div>
    </div>
  </div>

  <div v-else class="h-[85vh]">
    <EmptyState :data="{ description: 'You need pick a template from list', title: 'Pick Menu Templates' }">
      <template #button-empty-state>
        <div class="mt-4 block">
          <Button type="secondary" label="Templates" icon="fas fa-th-large" @click="isModalOpen = true"></Button>
        </div>
      </template>
    </EmptyState>
  </div>

  <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false">
    <HeaderListModal 
            :onSelectBlock
            :webBlockTypes="webBlockTypes.data.filter((item)=> item.component == 'menu')"
            :currentTopbar="usedTemplates"
        />
  </Modal>
</template>

<style scoped lang="scss">
:deep(.loading-overlay) {
    position: block;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.8);
    z-index: 1000;
}

:deep(.spinner) {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top: 4px solid #3498db;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
