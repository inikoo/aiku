<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 10:16:56 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, onBeforeMount  } from 'vue';
import VGrid from '@revolist/vue3-datagrid';
import { cloneDeep } from 'lodash';
import Button from '../Elements/Buttons/Button.vue';
import { faSave, faPlus } from '@/../private/pro-regular-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { router } from '@inertiajs/vue3'
import SelectTypePlugin from "@revolist/revogrid-column-select";
import Date from "@revolist/revogrid-column-date";
library.add(faSave,faPlus);
const props = defineProps({
  actionRoute: Object,
  theme: String,
  data: {
    type: Object,
  },
});

const plugin = { 'select': new SelectTypePlugin(), 'date': new Date() };
const addMultipleRows = () => {
  const result = [];
  const arrayData = cloneDeep(props.data.columns);
  for (let i = 0; i < 20; i++) {
    const data = {};
    for (const field of arrayData) {
      data[field.prop] = '';
    }
    result.push(data);
  }
  setData.value =  [...setData.value,...result];
};


let vgrid = ref()
let gRowIndex = ref(0)
let gColName = ref('')

const onBeforeEditStart = (e: CustomEvent<{ rowIndex: number, prop: string }>) => {
  gRowIndex.value = e.detail.rowIndex
  gColName.value = e.detail.prop 
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const onFocusOut = async (e: any) => { // I don't know about this event, no docs
  // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
  let viewData = await vgrid.value.$el.getVisibleSource()
  viewData[gRowIndex.value][gColName.value] = e.target.value
  setData.value = vgrid.value.source;
}

const handleSave = () => {
  const editableRows = [];
  for (const row of setData.value) {
    let isRowEditable = false;
    for (const column of props.data.columns) {
      if (row[column.prop] !== '') {
        isRowEditable = true;
        break;
      }
    }
    if (isRowEditable) {
      editableRows.push(row);
    }
  }
  router.post(props.actionRoute.name, editableRows);
};;

onBeforeMount(() => {
  addMultipleRows();
});
const setData = ref([]);
</script>

<template>
   <div class="flex justify-end gap-x-3.5">
    <div class="py-4 px-0 ">
      <Button type="white" @click="addMultipleRows">
        <span class="flex gap-3 items-center">
          <FontAwesomeIcon icon="far fa-plus" />
          <span>Add Row</span>
        </span>
      </Button>
    </div>
    <div class="py-4 px-0 ">
      <Button type="white">
        <span class="flex gap-2 items-center">
          <span>Cancel</span>
        </span>
      </Button>
    </div>
    <div class="py-4 pr-5 pl-0">
      <Button @click="handleSave">
        <span class="flex gap-3 items-center">
          <FontAwesomeIcon icon="far fa-save" />
          <span>Save</span>
        </span>
      </Button>
    </div>
  </div>

  <div class="py-0.5">
    <v-grid 
     ref="vgrid"
     @beforeeditstart="onBeforeEditStart" 
     @focusout="onFocusOut" 
     theme='material' 
     :source="setData"
     :columns="props.data.columns" 
     class="custom-grid" 
     :columnTypes='plugin'/>
  </div>
</template>

<style>
revo-grid {
  height: 650px;
  box-shadow: 0 1px 0 0 #f1f1f1, 0 1px 0 0 #f1f1f1 inset;
}

.custom-grid .rgHeaderCell {
  border-right: solid 1px #f1f1f1;
}

.custom-grid .rgCell {
  border-right: solid 1px #f1f1f1;
}

.custom-grid revo-grid[theme="material"] revogr-data .rgRow {
  box-shadow: 0 -1px 0 0 #f1f1f1 inset;
}
</style>
