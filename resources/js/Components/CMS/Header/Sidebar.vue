<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import Image from "@/Components/Image.vue"
  const props = defineProps<{
    data: Object;
}>();

const emits = defineEmits();

const changeLogo = async (element) => {
    emits('changeLogo',element)
};

  </script>
  
  <template>
      <div class="bg-white">
              <div class="w-[100px] sm:w-[200px] p-6 overflow-y-auto overflow-x-hidden h-[46rem]">
              <div class="font-medium text-2xl mb-2">Header Tools</div>
              <hr class="my-5" />
                  <div class="flex items-center justify-between">
                      <h2 class="text-sm font-medium text-gray-900">Logo</h2>
                  </div>
                  <label for="faviconUpload" class="flex justify-center items-center bg-white cursor-pointer">
                      <input type="file" id="faviconUpload" accept="image/*" style="display: none" @change="changeLogo" />
                      <Image :src="data.logoSrc" class="inline-block h-14 w-auto rounded-md my-2" />
                  </label>
  
                  <hr class="mt-5" />
              </div>
      </div>
  </template>
  