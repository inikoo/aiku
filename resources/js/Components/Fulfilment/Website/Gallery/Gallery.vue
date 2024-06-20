<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { faCube, faStar, faImage } from "@fas"
  import { library } from "@fortawesome/fontawesome-svg-core"
  import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
  import Modal from "@/Components/Utils/Modal.vue";
  import { layoutStructure } from '@/Composables/useLayoutStructure'
  import { inject } from 'vue'
  import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
  import Upload from './Upload.vue'
  
  library.add(faCube, faStar, faImage)
  
  const props = defineProps<{
      open: Boolean
      width?: String
  }>()

  const layout = inject('layout', layoutStructure)
  
  const emits = defineEmits<{
    (e: 'onClose'): void
}>()

const tabs = [
    {
        label : "Upload",
        key : 'upload',
    },
    {
        label : "Images Uploaded",
        key : 'images_uploaded',
    },
    {
        label : "Images Template",
        key : 'images_template',
    },
]

const getComponent = (componentName: string) => {
  const components: any = {
    'upload': Upload,
  };
  return components[componentName] ?? null;
};

  
  </script>
  
  <template>
       <Modal :isOpen="open" @onClose="()=>emits('onClose')" width="w-1/2">
        <TabGroup>
            <TabList class="flex space-x-8 border-b-2">
                <Tab v-for="tab in tabs" as="template" :key="tab.key" v-slot="{ selected }">
                    <button
                        :style="selected ? { color: layout.app.theme[0], borderBottomColor: layout.app.theme[0] } : {}"
                        :class="[
                            'whitespace-nowrap border-b-2 py-1.5 px-1 text-sm font-medium focus:ring-0 focus:outline-none mb-2',
                            selected
                                ? `border-org-5s00 text-[${layout.app.theme[0]}]`
                                : `border-transparent text-[${layout.app.theme[0]}] hover:border-[${layout.app.theme[0]}]`,
                        ]">
                        {{ tab.label }}
                    </button>
                </Tab>
            </TabList>

            <TabPanels class="mt-2">
                <TabPanel v-for="(tab, idx) in tabs" :key="idx" :class="[
                    'rounded-xl bg-white p-3',
                    'ring-white/60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2',
                ]">
                <component
							:is="getComponent(tab['key'])"
							 />

                </TabPanel>
            </TabPanels>
        </TabGroup>
        </Modal>
  </template>