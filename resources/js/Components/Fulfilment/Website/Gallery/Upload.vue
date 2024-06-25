<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { faCube, faStar, faImage } from "@fas"
  import { library } from "@fortawesome/fontawesome-svg-core"
  import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
  import Gallery from "../Gallery/Gallery.vue";
  import axios from 'axios'
  import { ref } from "vue"
  import { router } from '@inertiajs/vue3'

  library.add(faCube, faStar, faImage)
  
  const props = defineProps<{
      uploadRoutes: string
  }>()

const isDragging = ref(false);
const uploadedFilesList =ref([])
const fileInput = ref();


const onUpload = async () => {
  try {
    // Create a new FormData object
    const formData = new FormData();

    // Append each file to the FormData object
    uploadedFilesList.value.forEach((file, index) => {
      formData.append(`images[${index}]`, file);
    });

    // Make the POST request with FormData
    const response = await axios.post(props.uploadRoutes, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    console.log('saved', response);
  } catch (error) {
    console.error('error', error);
  }
};

  
  const addComponent = (element) => {
     uploadedFilesList.value = element.target.files;
     console.log(uploadedFilesList)
     onUpload()
  }
  
  const dragover = (e) => {
      e.preventDefault();
      isDragging.value = true;
  };
  
  const dragleave = () => {
      isDragging.value = false;
  };
  
  const drop = (e) => {
      e.preventDefault();
      uploadedFilesList.value = e.dataTransfer.files;
      isDragging.value = false;
      onUpload()
  };    
  
  </script>
  
  <template>
      <div type="button" @dragover="dragover" @dragleave="dragleave" @drop="drop"
          class="relative block h-full w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 p-auto">
          <font-awesome-icon :icon="['fas', 'image']" class="mx-auto h-12 w-12 text-gray-400" />
          <label class="bg-transparent inset-0 absolute inline-block cursor-pointer" id="input-slide-large-mask"
              for="fileInput" />
          <input ref="fileInput" type="file" multiple name="file" id="fileInput"  accept="image/*"
              class="absolute cursor-pointer rounded-md border-gray-300 sr-only" @change="addComponent" />
          <span class="mt-2 block text-sm font-semibold text-gray-900">Drag and Drop Image Here</span>
          <span class="mt-2 block text-xs font-semibold text-gray-300">Click here to upload</span>
      </div>

  
  </template>