<script setup lang="ts">
import { ref, onMounted, inject, toRaw } from 'vue'
import draggable from "vuedraggable";
import Button from '@/Components/Elements/Buttons/Button.vue';
import Popover from '@/Components/Popover.vue'
import Dialog from 'primevue/dialog';
import DialogEditLink from '@/Components/CMS/Website/Menus/EditMode/DialogEditLink.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faTrashAlt } from '@fas';
import { faHeart } from '@far';
import PureInput from '@/Components/Pure/PureInput.vue';
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue';
import { v4 as uuidv4 } from "uuid"
import EmptyState from '@/Components/Utils/EmptyState.vue';
import DialogEditName from '@/Components/CMS/Website/Menus/EditMode/DialogEditName.vue';

library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faTrashAlt);

interface navigation {
  label: String
  id: Number | String
  type: String
}

const props = defineProps<{
  Navigation: Array,
  selectedNav: Number | null
}>()

const visibleNameDialog = ref(false)
const visibleDialog = ref(false)
const nameValue = ref<navigation | null>()
const linkValue = ref<navigation | null>()
const parentIdx = ref<Number>(0)
const linkIdx = ref<Number>(0)

const addLink = (data: Object) => {
  data.links.push(
    { label: "New Link", link: "", id: uuidv4() }
  )
}

const deleteLink = (data: Array, index: Number) => {
  data.splice(index, 1)
}

const changeType = (type: string, data: Object) => {
  if (type == 'multiple') data['subnavs'] = []
}

const addCard = () => {
  props.Navigation[props.selectedNav].subnavs.push(
    {
      title: "New Navigation",
      id: uuidv4(),
      links: [
        { label: "New nav", link: "", id: uuidv4() },
      ],
    },
  )
}

const deleteNavCard = (data, index) => {
  props.Navigation[props.selectedNav].subnavs.splice(index, 1)
}

const onNameClick = (data = null, index = -1) => {
  visibleNameDialog.value = true
  nameValue.value = toRaw({ ...data, index: index })
}

const onChangeName = (data) => {
  props.Navigation[props.selectedNav].subnavs[data.index] = data
  visibleNameDialog.value = false
  nameValue.value = null
}

const onLinkClick = (data = null, parentIndex = -1, index = -1) => {
  visibleDialog.value = true
  parentIdx.value = parentIndex
  linkIdx.value = index
  linkValue.value = toRaw({ ...data })
}

const onChangeLink = (data) => {
  props.Navigation[props.selectedNav].subnavs[parentIdx.value].links[linkIdx.value] = {
    ...props.Navigation[props.selectedNav].subnavs[parentIdx.value].links[linkIdx.value],
    label: data.label,
    link: data.link,
  }
  linkValue.value = null
  parentIdx.value = -1
  linkIdx.value  = -1
  visibleDialog.value = false
}


</script>

<template>
  <div class="bg-slate-100">
    <div class="grid grid-flow-row-dense grid-cols-4 gap-4 px-4 pt-4 bg-slate-10">
      <div v-if="Navigation[selectedNav]" class="bg-white py-2 rounded-lg m-1 col-span-1 px-2 cursor-grab">
        <div class="font-bold text-xs mb-3">Navigation Title :</div>
        <PureInput v-model="Navigation[selectedNav].label"></PureInput>
      </div>

      <div v-if="Navigation[selectedNav]" class="bg-white py-2 rounded-lg m-1 col-span-1 px-2 cursor-grab">
        <div class="font-bold text-xs mb-3">Type :</div>
        <PureMultiselect :required="true" v-model="Navigation[selectedNav].type" label="label" value-prop="value"
          @-on-change="(e) => changeType(e, Navigation[selectedNav])"
          :options="[{ label: 'Single', value: 'single' }, { label: 'Multiple', value: 'multiple' }]">
        </PureMultiselect>
      </div>


      <div v-if="Navigation[selectedNav] && Navigation[selectedNav].type == 'single'"
        class="bg-white py-2 rounded-lg m-1 col-span-1 px-2 cursor-grab">
        <div class="font-bold text-xs mb-3">Link :</div>
        <PureInput v-model="Navigation[selectedNav].link"></PureInput>
      </div>


      <div
        v-if="Navigation[selectedNav] && Navigation[selectedNav].type == 'multiple' && Navigation[selectedNav]?.subnavs?.length <= 7"
        class="bg-white py-2 rounded-lg m-1 col-span-1 px-2 cursor-grab">
        <div class="font-bold text-xs mb-3">Action :</div>
        <Button type="create" label="Add Card" size="xs" @click="addCard"></Button>
      </div>

    </div>

    <draggable v-if="Navigation[selectedNav] && Navigation[selectedNav].type == 'multiple'" :animation="200"
      :list="Navigation[selectedNav].subnavs" ghost-class="ghost" group="subnav" itemKey="id"
      class="grid grid-flow-row-dense grid-cols-4 gap-4 p-4 bg-slate-10">
      <template #item="{ element, index }">
        <div class="bg-white h-[26rem] rounded-lg p-4 col-span-1 cursor-grab">
          <div class="flex justify-between">
            <div class="font-bold text-xs mb-3" @click="() => onNameClick(element, index)">{{ element.title }}</div>
            <div>
              <font-awesome-icon v-if="element.links.length < 8" icon="fas fa-plus-circle"
                @click="() => addLink(element)" class="cursor-pointer text-gray-400 mb-3 mr-3"></font-awesome-icon>
              <font-awesome-icon icon="fas fa-trash-alt" class="cursor-pointer text-red-400 mb-3"
                @click="() => deleteNavCard(element, index)"></font-awesome-icon>
            </div>
          </div>

          <draggable :list="element.links" ghost-class="ghost" group="link" itemKey="id" :animation="200"
            class="flex flex-col gap-y-2 p-3 relative">
            <template #item="{ element: link, index: linkIndex }">

              <div class="flex items-center gap-x-2 p-1">
                <font-awesome-icon icon="fas fa-bars" class="text-[13px] text-gray-400 pr-2"></font-awesome-icon>
                <span class="text-gray-500 hover:text-gray-600 hover:underline cursor-pointer text-xs"
                  @click="() => onLinkClick(link, index, linkIndex)">{{ link.label }}</span>
              </div>


            </template>
          </draggable>
        </div>
      </template>

    </draggable>


    <div v-if="Navigation[selectedNav]?.subnavs?.length == 0 && Navigation[selectedNav].type == 'multiple'">
      <EmptyState :data="{ title: 'you dont have Any Navigation', description: '' }" />
    </div>


    <Dialog v-model:visible="visibleNameDialog" modal header="Edit Name" :style="{ width: '25rem' }">
      <DialogEditName :data_form="nameValue" @on-save="onChangeName" />
    </Dialog>

    <Dialog v-model:visible="visibleDialog" modal header="Edit Link" :style="{ width: '25rem' }">
      <DialogEditLink :modelValue="linkValue" :parent="nameValue" @on-save="onChangeLink" />
    </Dialog>

  </div>
</template>


<style scss></style>
