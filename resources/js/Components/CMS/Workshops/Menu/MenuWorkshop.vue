<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { navigation } from './Descriptor.ts'
import draggable from "vuedraggable";
import Button from '@/Components/Elements/Buttons/Button.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes } from '@fas';
import { faHeart } from '@far';

library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes);

const Navigation = ref(navigation)
const selectedNav = ref(null)



</script>

<template>
  <div class="grid grid-flow-row-dense grid-cols-4">
    <div class="col-span-1 h-screen bg-slate-200 px-3 py-2">
      <div class="flex justify-between">
        <div class="font-bold text-sm">Navigations : </div>
        <Button type="create" label="Add Navigation" size="xs"></Button>
      </div>
      <draggable :list="Navigation" ghost-class="ghost" group="column" itemKey="id" class="mt-2 space-y-1">
        <template #item="{ element, index }">
          <div @click="selectedNav = element"
            class="flex-auto rounded-md p-3 ring-1 ring-inset ring-gray-200 bg-white cursor-grab">
            <div class="flex justify-between gap-x-4">
              <div class="py-0.5 text-xs leading-5 text-gray-500">
                <span class="font-medium text-gray-900">{{ element.label }}</span>
              </div>
              <div class="flex-none py-0 text-xs leading-5 text-gray-500 cursor-pointer">
                <font-awesome-icon :icon="['fal', 'times']" />
              </div>
            </div>
          </div>
        </template>
      </draggable>
    </div>
    <div class="col-span-3">
      <div>
        <draggable v-if="selectedNav" :list="selectedNav.subnavs" ghost-class="ghost" group="subnav" itemKey="id"
          class="grid grid-flow-row-dense grid-cols-4 bg-slate-100 h-screen gap-4 p-4">
          <template #item="{ element, index }">
            <div class="bg-white h-[26rem] rounded-lg p-4 col-span-1 cursor-grab">
              <div class="font-bold text-xs mb-3">{{ element.title }}</div>
              <div v-for="link in element.links" class="flex flex-col gap-y-2 p-3">
                <div class="flex items-center gap-x-2">
                  <font-awesome-icon icon="fas fa-chevron-right" class="text-[10px] text-gray-400"></font-awesome-icon>
                  <span class="text-gray-500 hover:text-gray-600 hover:underline cursor-pointer text-xs">{{ link}}</span>
                </div>
              </div>
            </div>
          </template>
        </draggable>

      </div>
    </div>
  </div>

</template>


<style scss></style>
