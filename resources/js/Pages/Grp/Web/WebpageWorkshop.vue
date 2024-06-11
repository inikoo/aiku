<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { Head } from '@inertiajs/vue3';
  import PageHeading from '@/Components/Headings/PageHeading.vue';
  import { capitalize } from "@/Composables/capitalize";
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { ref } from 'vue';
  import { faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faCoins, faTimes, faBrowser, faStars, faNewspaper, faRectangleWide } from '@fal';
  import draggable from "vuedraggable";
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import Button from '@/Components/Elements/Buttons/Button.vue';
  import Modal from "@/Components/Utils/Modal.vue";
  import BlockList from './Block/BlockList.vue';
  import WowsbarBanner from './Block/WowsbarBanner.vue';
  import ProductPage from './Block/ProductPage.vue';
  import Text from './Block/TextContent.vue';
  import FamilyPageOffer from './Block/FamilyPage-offer.vue';
  import ProductList from './Block/ProductList.vue'
  import CTA from './Block/CTA.vue'
  import Rewiews from './Block/Reviews.vue'
  import Image from './Block/Image.vue'
  import CTA2 from './Block/CTA2.vue'
  import Gallery from './Block/Gallery.vue'
  import Iframe from './Block/Iframe.vue'
  import axios from 'axios';
  import Action from "@/Components/Forms/Fields/Action.vue";
  import debounce from 'lodash/debounce';
  
  library.add(faCoins, faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faTimes, faBrowser, faStars, faNewspaper, faRectangleWide);
  
  const props = defineProps<{
    title: string,
    pageHead: PageHeadingTypes,
    webpage: Object
  }>();
  
  const openModal = ref(false);
  const data = ref(props.webpage.compiled_layout);
  
  const sendUpdate = async () => {
    try {
      await axios.patch(route(props.webpage.update_route.name, props.webpage.update_route.parameters), { compiled_layout: data.value });
      console.log('saved');
    } catch (error: any) {
      console.log('error', error);
    }
  };
  
  const debouncedSendUpdate = debounce(sendUpdate, 1000);
  
  const onUpdated = () => {
    debouncedSendUpdate();
  };
  
  const getComponent = (componentName: string) => {
    const components: any = {
      'bannerWowsbar': WowsbarBanner,
      'ProductPage': ProductPage,
      'text': Text,
      'FamilyPageOffer': FamilyPageOffer,
      'ProductList' : ProductList,
      'CTA' : CTA,
      'CTA2' : CTA2,
      'Reviews' : Rewiews,
      'Image' : Image,
      'Gallery' : Gallery,
      "Iframe" : Iframe
    };
    return components[componentName] ?? null;
  };
  
  const onPickBlock = (e) => {
    data.value.push(e);
    openModal.value = false;
    onUpdated();
  };
  
  const deleteBlock = (index) => {
    data.value.splice(index, 1);
    onUpdated();
  };
  
  const setData = () => {
    console.log(data.value);
  };
  </script>
  

  <template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
      <template #button-publish="{ action }">
        <Action v-if="action.action" :action="action.action" :dataToSubmit="data" />
      </template>
    </PageHeading>
    <div class="mx-auto px-4 py-4 sm:px-6 lg:px-8 w-full h-screen">
      <div class="mx-auto grid grid-cols-4 gap-1 lg:mx-0 lg:max-w-none">
        <div class="col-span-3 h-screen overflow-auto border-2 border-dashed">
          <div v-if="data.length == 0"
            class="relative block w-full h-full border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
            <span class="mt-2 block text-sm font-semibold text-gray-900">You dont have block</span>
          </div>
          <div v-else>
            <div v-for="(activityItem, activityItemIdx) in data" :key="activityItem.id" class="w-full">
              <component :is="getComponent(activityItem['component'])" :key="activityItemIdx"
                v-bind="activityItem.fieldData" v-model="activityItem.fieldValue" @autoSave="()=>onUpdated()" />
            </div>
          </div>
        </div>
        <div class="col-span-1 h-screen">
          <div class="border-2 bg-gray-200 p-3 h-full">
            <div class="flex justify-between">
              <h2 class="text-sm font-semibold leading-6 text-gray-900">Block List</h2>
              <Button label="Block" type="create" size="xs" @click="() => openModal = true" />
            </div>
            <draggable :list="data" v-if="data.length > 0" ghost-class="ghost" group="column"
              itemKey="column_id" class="mt-2 space-y-1">
              <template #item="{ element, index }">
                <div class="flex-auto rounded-md p-3 ring-1 ring-inset ring-gray-200 bg-white cursor-grab">
                  <div class="flex justify-between gap-x-4">
                    <div class="py-0.5 text-xs leading-5 text-gray-500">
                      <span class="font-medium text-gray-900">{{ element.name }}</span>
                    </div>
                    <div class="flex-none py-0 text-xs leading-5 text-gray-500 cursor-pointer" @click="() => deleteBlock(index)">
                      <font-awesome-icon :icon="['fal', 'times']" />
                    </div>
                  </div>
                </div>
              </template>
            </draggable>
            <div v-else :style="{ height: 'calc(100vh - 8%)' }"
              class="relative mt-4 block rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
              <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
              <span class="mt-2 block text-sm font-semibold text-gray-900">You dont have block</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Modal :isOpen="openModal" @onClose="openModal = false">
      <BlockList :onPickBlock="onPickBlock" />
    </Modal>
    <div @click="setData">see data</div>
  </template>
  