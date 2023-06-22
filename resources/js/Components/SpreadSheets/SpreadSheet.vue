<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 10:16:56 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, onBeforeMount, watch  } from 'vue';
import VGrid from '@revolist/vue3-datagrid';
import { cloneDeep } from 'lodash';
import Button from '../Elements/Buttons/Button.vue';
import { faSave, faPlus } from '@/../private/pro-regular-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { router } from '@inertiajs/vue3'
import { useDatabaseList,  } from "vuefire"
import { getDatabase, ref as dbRef, set, onValue } from 'firebase/database';
import { initializeApp } from "firebase/app"
import serviceAccount from "../../../../storage/app/aiku-firebase.json";

library.add(faSave, faPlus);

const props = defineProps({
  actionRoute: Object,
  theme: String,
  data: {
    type: Object,
  },
});

const numberInputed = ref(1);
const firebaseApp = initializeApp(serviceAccount);
const db = getDatabase(firebaseApp);
const setData = ref([]);

const updateData = async () => {
  try {
    await set(dbRef(db, 'aiku_multi_create_'), setData.value);
    console.log('Data successfully updated!');
  } catch (error) {
    console.error('Error updating data:', error);
  }
};

watch(setData, updateData, { deep: true });

console.log(setData);

const columns = [
  {
    id: '*',
    name: '*',
    columnType: '*',
    prop: '*',
    readonly: true, // Set the column as readonly
  },
  ...props.data.columns,
];

const addMultipleRows = () => {
  const result = [];
  const arrayData = cloneDeep(columns);
  for (let i = 0; i < 20; i++) {
    const data = {};
    for (const field of arrayData) {
      data[field.prop] = '';
    }
    result.push(data);
  }
  setData.value = [...setData.value, ...result];
};

const addRows = () => {
  const result = [];
  const arrayData = cloneDeep(columns);
  for (let i = 0; i < numberInputed.value; i++) {
    const data = {};
    for (const field of arrayData) {
      data[field.prop] = '';
    }
    result.push(data);
  }
  setData.value = [...setData.value, ...result];
};

let vgrid = ref();
let gRowIndex = ref(0);
let gColName = ref('');

const onBeforeEditStart = (e) => {
  gRowIndex.value = e.detail.rowIndex;
  gColName.value = e.detail.prop;
};

const onFocusOut = async (e) => {
  let viewData = await vgrid.value.$el.getVisibleSource();
  viewData[gRowIndex.value][gColName.value] = e.target.value;
  setData.value = vgrid.value.source;
};

const handleSave = () => {
  const editableRows = [];
  for (const row of setData.value) {
    let isRowEditable = false;
    const result = {};
    for (const column of props.data.columns) {
      if (row[column.prop] !== '') {
        isRowEditable = true;
        result[column.prop] = row[column.prop];
      }
    }
    if (isRowEditable) {
      editableRows.push(result);
    }
  }
  console.log('datasend', editableRows);
  router.post(props.actionRoute.name, editableRows);
};

onBeforeMount(() => {
  console.log('value', setData.value.length);
  if (setData.value.length < 1) {
    addMultipleRows();
  }
});

// Update `setData` with real-time data from Firebase
onValue(dbRef(db, 'sheetCreate'), (snapshot) => {
  const data = snapshot.val();
  setData.value = data ? Object.values(data) : [];
});

</script>

<template>
  <div class="flex justify-end gap-x-3.5">
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
    <v-grid ref="vgrid" @beforeeditstart="onBeforeEditStart" @focusout="onFocusOut" theme='material' :source="setData"
      :columns="columns" class="custom-grid" />

  </div>

  <div class="pt-4 pb-6 pl-1 pr-0 flex">
    <button type="button" @click="addRows"
      class="flex rounded-r-none items-center gap-3 bg-white border border-gray-300 py-2 px-4 rounded">
      <FontAwesomeIcon icon="far fa-plus" />
      <span>Add Row</span>
    </button>
    <input type="number" v-model="numberInputed"
      class="border border-gray-300 px-4 py-2 rounded-l-none rounded rounded-l-none custom-input">
  </div>


  <!-- <div class="py-0.5">
   <iframe width="100%" height="600px" src="https://docs.google.com/spreadsheets/d/1vcDD6Mb9QIAXV2dfGkkzQlEcR8VbSddfRTjPMYf0zJQ/edit#gid=0"></iframe>
  </div> -->
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

.custom-input {
  border-left: none;
  width: 120px;
}
</style>
