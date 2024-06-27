<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import draggable from "vuedraggable";
import Button from '@/Components/Elements/Buttons/Button.vue';
import Popover from '@/Components/Popover.vue'

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars } from '@fas';
import { faHeart } from '@far';
import PureInput from '@/Components/Pure/PureInput.vue';
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue';
import { v4 as uuidv4 } from "uuid"

library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars);

const props = defineProps<{
    Navigation: Array,
    selectedNav : Number | null
}>()


const addLink = (data) => {
  data.links.push(
    { label: "New Link", link: "", id: uuidv4() }
  )
}

const deleteLink = (data, index) => {
  data.splice(index, 1)
}



</script>

<template>
      <div class="bg-slate-100 h-screen overflow-auto">
        <div class="grid grid-flow-row-dense grid-cols-4 gap-4 px-4 pt-4 bg-slate-10">
          <div v-if="Navigation[selectedNav]" class="bg-white py-2 rounded-lg m-1 col-span-1 px-2 cursor-grab">
            <div class="font-bold text-xs mb-3">Navigation Title :</div>
            <PureInput v-model="Navigation[selectedNav].label"></PureInput>
          </div>

          <div v-if="Navigation[selectedNav]" class="bg-white py-2 rounded-lg m-1 col-span-1 px-2 cursor-grab">
            <div class="font-bold text-xs mb-3">Type :</div>
            <PureMultiselect :required="true" v-model="Navigation[selectedNav].type" label="label" value-prop="value"
              :options="[{ label: 'Single', value: 'single' }, { label: 'Multiple', value: 'multiple' }]">
            </PureMultiselect>
          </div>


          <div v-if="Navigation[selectedNav] && Navigation[selectedNav].type == 'single'"
            class="bg-white py-2 rounded-lg m-1 col-span-1 px-2 cursor-grab">
            <div class="font-bold text-xs mb-3">link :</div>
            <PureInput v-model="Navigation[selectedNav].label"></PureInput>
          </div>
        </div>


        <draggable v-if="selectedNav" :list="Navigation[selectedNav].subnavs" ghost-class="ghost" group="subnav"
          itemKey="id" class="grid grid-flow-row-dense grid-cols-4 gap-4 p-4 bg-slate-10">
          <template #item="{ element, index }">
            <div class="bg-white h-[26rem] rounded-lg p-4 col-span-1 cursor-grab">
              <div class="flex justify-between">
                <div class="font-bold text-xs mb-3">{{ element.title }}</div>
                <div v-if="element.links.length < 8"><font-awesome-icon icon="fas fa-plus-circle"
                    @click="() => addLink(element)" class="cursor-pointer text-gray-400 mb-3"></font-awesome-icon></div>
              </div>

              <draggable :list="element.links" ghost-class="ghost" group="link" itemKey="id"
                class="flex flex-col gap-y-2 p-3 relative">
                <template #item="{ element: link, index: linkIndex }">

                  <div class="flex items-center gap-x-2 p-1">
                    <font-awesome-icon icon="fas fa-bars" class="text-[13px] text-gray-400 pr-2"></font-awesome-icon>
                    <Popover position="">
                      <template #button>
                        <span class="text-gray-500 hover:text-gray-600 hover:underline cursor-pointer text-xs">{{
                          link.label }}</span>
                      </template>

                      <template #content="{ close: closed }">
                        <div class="p-1 my-1">
                          <div class="font-bold text-xs mb-1">Label :</div>
                          <PureInput v-model="link.label"></PureInput>
                        </div>

                        <div class="p-1 mb-1">
                          <div class="font-bold text-xs mb-1">Link :</div>
                          <PureInput v-model="link.link"></PureInput>
                        </div>

                        <div class="p-1 mb-1">
                          <Button type="delete" label="" size="xs"
                            @click="() => deleteLink(element.links, linkIndex)"></Button>
                        </div>

                      </template>
                    </Popover>
                  </div>


                </template>
              </draggable>
            </div>
          </template>
        </draggable>

      </div>
</template>


<style scss></style>
